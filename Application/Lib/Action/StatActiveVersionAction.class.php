<?php

class StatActiveVersionAction extends CommonAction {

public $table_name = "t_stat_daily_active_report";
	public $db_name = DB_STAT;
	public $primary_key = "stat_dt"; //主键
	function __construct(){
		parent::__construct();
	}
	public function index(){
		$table_title = array(
			  "stat_dt"		=>"统计时间",
			  "type_name"		=>"版本号",
			  "type_value"		=>"数量",
		);
		$this->assign("table_title",$table_title);
		$db = M($this->table_name,null,$this->db_name);
		$data = $db->field(" DISTINCT type_id,type_name")->where("module_id = 3")->order("type_name desc")->select();
		$this->assign("version",$data);
		$this->display();
	}
	public function getList(){
		parent::getList();
		$table = M($this->table_name,null,$this->db_name);	
		$c = $this->type_id == ""?"":"type_id = '{$this->type_id}' and ";
		$et = empty($this->et)?"":" and stat_dt <='{$this->et}' ";
		$where = " $c
				module_id  = 3
				and stat_dt >='{$this->bt}'
				$et
		";
		$data = $table->where($where)->order("stat_dt DESC,module_id desc,type_id ASC")->select();
		if(!empty($data)){
			foreach ($data as $each){
				$sum += $each["type_value"];
			}
			$data[] = array(
					"stat_dt" => "合计",
// 					"module_name" => "合计",
					"type_value" => $sum
			);
		}
		$totalRecords = $table->where($where)->count();
		$table_data["total"] = $totalRecords;
		$table_data["rows"] = empty($data)?array():$data;
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