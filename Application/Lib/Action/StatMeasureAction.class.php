<?php

class StatMeasureAction extends CommonAction {

public $table_name = "t_stat_daily_measure_report";
	public $db_name = DB_STAT;
	public $primary_key = "stat_dt"; //主键
	function __construct(){
		parent::__construct();
	}
	public function index(){
		$table_title = array (
				"stat_dt" => "统计时间",
				"module_name" => "测量项目",
				"1" => "手动输入",
				"2" => "设备测量",
				"3" => "39健康管家APP",
				"4" => "PAD版",
				"sum" => "合计" 
		);
		$this->assign("table_title",$table_title);
		$db = M($this->table_name,null,$this->db_name);	
		$item = $db->field("DISTINCT module_id,module_name ")->select();
		$this->assign("item",$item);
		$this->display();
	}
	public function getList(){
		parent::getList();
		if(empty($this->bt)) return ;
		$table = M($this->table_name,null,$this->db_name);	
		
		$where = " type_id in(1,2,3,4) and stat_dt >= '{$this->bt}' and stat_dt <= '{$this->et}' ";
		if(!empty($this->module_id)){
			$where .=" and module_id = {$this->module_id}";
		}		
		$data = $table->where($where)->order("stat_dt DESC,module_id desc,type_id ASC ,type_value desc")->select();
		$result = array();
// 		print_r($data);exit;
		foreach ($data as $each){
			$result[$each["stat_dt"].$each["module_id"]]["module_name"] = $each["module_name"];
			$result[$each["stat_dt"].$each["module_id"]]["stat_dt"] = $each["stat_dt"];
			$result[$each["stat_dt"].$each["module_id"]][$each["type_id"]] = $each["type_value"];
			$result[$each["stat_dt"].$each["module_id"]]["sum"] = $result[$each["stat_dt"].$each["module_id"]][1] + $result[$each["stat_dt"].$each["module_id"]][2];
		}
		if (!empty($data)){
			foreach ($result as $each){
				foreach ($each as $k=>$val){
					$sum_sum[$k] += $val;
				}
			}
			$sum_sum["stat_dt"] = "累计";
			$sum_sum["module_name"] = "";
			array_push($result, $sum_sum);
		}

		$data = array_values($result);
		$totalRecords = $table->where($where)->count();
		$table_data["total"] = $totalRecords;
		$table_data["rows"] = empty($data)?array(array("stat_dt"=>"无数据")):$data;
		$this->ajaxReturn($table_data);
	}

	public function change(){
		$mod = I(2);
		if(empty($mod)) return ;
		$db = M($this->table_name,null,$this->db_name);
		$data = $db->field("type_name as text, type_id as value")->where("module_id = $mod")->group("type_id")->select();
		$data[0]["selected"] = true;
		$this->ajaxReturn($data);
		
	}
	
	
	
}