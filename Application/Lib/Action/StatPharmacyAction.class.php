<?php

class StatPharmacyAction extends CommonAction {

public $table_name = "t_stat_daily_pharmacy_report";
	public $db_name = DB_STAT;
	public $primary_key = "stat_dt"; //主键
	function __construct(){
		parent::__construct();
	}
	public function index(){
		$table_title = array (
				"stat_dt" => "统计时间",
				"pharmacy_name" => "药店",
				"1" => "总人数",
				"4" => "新增人数",
				"2" => "体重",
				"3" => "血压",
		);
		$this->assign("table_title",$table_title);
		$db_query = M("t_pharmacy_brand_info",null,DB_QUERY);
		$brand_info = $db_query->select();
		$this->assign("brand_info",$brand_info);
		$db = M("t_pharmacy_info",null,DB_QUERY);
		$phramacy_data = $db->select();
		$this->assign("phramacy_data",$phramacy_data);
		$this->display();
	}
	public function getList(){
		parent::getList();
		$table = M($this->table_name,null,$this->db_name);	
		$this->bt = empty($this->bt)?date("Y-m-d",time()-24*3600):$this->bt;
		$where = empty($this->brand_id)?"":"brand_id = {$this->brand_id}";
		$phramacy_id = array();
		$db_query = M("t_pharmacy_info",NULL,DB_QUERY);
		$phramacy_data = $db_query->field("pharmacy_id,pharmacy_name")->where($where)->select();
		foreach ($phramacy_data as $each){
			$phramacy_id[] = $each["pharmacy_id"];
			$phramacy_info[$each["pharmacy_id"]] = $each["pharmacy_name"];
		}
		if(!empty($this->phramacy_id)){
			$phramacy_id = array();
			$phramacy_id[] = $this->phramacy_id;
		}
// 		print_r($phramacy_id);exit;
		$phramacy_id = implode(",", $phramacy_id);
		$where = " stat_dt >= '{$this->bt}' and stat_dt <= '{$this->et}' and type_id in ($phramacy_id) and module_id IN (1,2,3,4)";
		$data = $table->where($where)->order("stat_dt DESC,module_id DESC,type_value desc")->select();
		$result = array();
		foreach ($data as $each){
			$result[$each["type_id"].$each["stat_dt"]]["pharmacy_name"] = $phramacy_info[$each["type_id"]];
			$result[$each["type_id"].$each["stat_dt"]]["stat_dt"] = $each["stat_dt"];
			$result[$each["type_id"].$each["stat_dt"]][$each["module_id"]] = $each["type_value"];
			$result[$each["type_id"].$each["stat_dt"]]["type_id"] = $each["type_id"];
			
		}
// 		print_r($result);exit;
		if (!empty($data)){
			foreach ($result as $each){
				foreach ($each as $k=>$val){
					$sum_sum[$k] += $val;
				}
			}
			$sum_sum["stat_dt"] = "累计";
			$sum_sum["pharmacy_name"] = "";
			array_push($result, $sum_sum);
		}
		$data = array_values($result);
		$totalRecords = $table->where($where)->count();
		$table_data["total"] = $totalRecords;
		$table_data["rows"] = empty($data)?array(array("stat_dt"=>"无数据")):$data;
		$this->ajaxReturn($table_data);
	}

	public function load_phramacy(){
		$db_query = M("t_pharmacy_info",null,DB_QUERY);
		$phramacy_id = I(2);
		if(!empty($phramacy_id)){
			$where = "brand_id = $phramacy_id";
		}
		$data = $db_query->field("pharmacy_id as value,pharmacy_name as text")->where($where)->select();
		$data = empty($data)?array():$data;
		$first = array(
			"value"	=>"",
			"text"	=>"全部"
		);
		array_unshift($data, $first);
		$this->ajaxReturn($data);
	}
	
	
	
	
}