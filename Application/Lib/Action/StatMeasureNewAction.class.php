<?php

class StatMeasureNewAction extends CommonAction {

public $table_name = "t_stat_daily_measure_report";
	public $db_name = DB_STAT;
	public $primary_key = "stat_dt"; //主键
	function __construct(){
		parent::__construct();
	}
	public function index(){
		$table_title = array (
				"stat_dt" => "统计时间",
				"1" => "血压",
				"2" => "心率",
				"3" => "血糖",
				"4" => "计步",
				"5" => "睡眠",
				"6" => "身高",
				"7" => "体重",
				"8" => "骨量",
				"9" => "内脂脂肪率",
				"10" => "水分率",
				"11" => "基础代谢",
				"12" => "肌肉率",
				"13" => "体质指数",
				"14" => "脂肪率",
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
		
		$where = "type_id <5 and stat_dt >= '{$this->bt}' and stat_dt <= '{$this->et}' ";
// 		if(!empty($this->module_id)){
// 			$where .=" and module_id = {$this->module_id}";
// 		}		
		$data = $table->where($where)->order("stat_dt DESC,module_id desc,type_id ASC")->select();
		$result = array();
		foreach ($data as $each){
			$result[$each["stat_dt"]]["stat_dt"] = $each["stat_dt"];
			$single_ret[$each["stat_dt"]][$each["module_id"]][] = $each["type_value"];
			$result[$each["stat_dt"]][$each["module_id"]] = array_sum($single_ret[$each["stat_dt"]][$each["module_id"]])/2;
			$tmp_sum[$each["stat_dt"]][$each["module_id"]] = $result[$each["stat_dt"]][$each["module_id"]];
			$result[$each["stat_dt"]]["sum"] = array_sum($tmp_sum[$each["stat_dt"]]);
		}
		$s = array();
		foreach ($result as $key=>$each){
			foreach ($each as $k=>$v){
				$s[$k] +=$v;
			}	
		}
		$s["stat_dt"] = "累计";
		if (!empty($result)){
			array_push($result, $s);
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