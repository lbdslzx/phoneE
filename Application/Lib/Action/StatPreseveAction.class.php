<?php

class StatPreseveAction extends CommonAction {

public $table_name = "t_stat_daily_preseve_report";
	public $db_name = DB_STAT;
	public $primary_key = "stat_dt"; //主键
	function __construct(){
		parent::__construct();
	}
	public function index(){
		$table_title = array(
			"type_id"		=>"用户注册时间",
			"reg_cnt"		=>"注册数",
//			"1val"		=>"一天后留存",
			"1rate"		=>"一天后留存率",
//			"2val"		=>"两天后留存",
			"2rate"		=>"两天后留存率",
	//		"3val"		=>"三天后留存",
			"3rate"		=>"三天后留存率",				
		//	"4val"		=>"四天后留存",
			"4rate"		=>"四天后留存率",
			//"5val"		=>"五天后留存",
			"5rate"		=>"五天后留存率",
//			"6val"		=>"六天后留存",
			"6rate"		=>"六天后留存率",
//			"7val"		=>"七天后留存",
			"7rate"		=>"七天后留存率",
// 			  "action"			=>"操作提示"
		);
		$this->assign("table_title",$table_title);
		$this->display();
	}
	public function getList(){
		parent::getList();
		$table = M($this->table_name,null,$this->db_name);	
		
		$where = "
				type_id  >= '$this->bt' and  type_id <= '$this->et'
		";
		if(empty($this->bt)){
			$where = "";
		}
		$dataSet = $table->where($where)->order("type_id DESC")->select();
		$data = array();
		foreach ($dataSet as $key=>$each){
			$bt = $each["type_id"];
			$st = $each["stat_dt"];
			$diff = strtotime($st)-strtotime($bt);
			$day = $diff/86400;
			$data[$each["type_id"]][$day."rate"] = (($each["type_value"]/$each["reg_cnt"])*100);
			$data[$each["type_id"]][$day."rate"] = round($data[$each["type_id"]][$day."rate"],2)."%";
			$data[$each["type_id"]][$day."val"] = $each["type_value"];
			$data[$each["type_id"]]["type_id"] = $each["type_id"];
// 			$data[$each["type_id"]]["stat_dt"] = $each["stat_dt"];
			$data[$each["type_id"]]["reg_cnt"] = $each["reg_cnt"];
// 			$data[$key]["preseve_rate"] = (($each["type_value"]/$each["reg_cnt"])*100)."%";
		}
		$data = array_values($data);
// 		$totalRecords = $table->where($where)->count();
		$table_data["total"] = $totalRecords;
		$table_data["rows"] = empty($data)?array():$data;
		$this->ajaxReturn($table_data);
	}

	
	
}