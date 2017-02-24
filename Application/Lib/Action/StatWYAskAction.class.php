<?php

class StatWYAskAction extends CommonAction {

	function __construct(){
		parent::__construct();
		$this->table_name = "t_stat_daily_ask_report";
		$this->db_name = DB_STAT;
		
	}
	public function index(){
		$table_title = array(
			  "stat_dt"		=>"统计时间",
			  "12"		=>"询问人数",
			  "11"		=>"询问次数",
				"22"		=>"采纳人数",
				"21"		=>"采纳次数",
				"32"		=>"回复人数",
				"31"		=>"回复次数",
			  "sum"		=>"合计",
// 			  "action"			=>"操作提示"
		);
		$this->assign("table_title",$table_title);
		$this->display();
	}
	public function getList(){
		parent::getList();
		$table = M($this->table_name,null,$this->db_name);	
		$et = empty($this->et)?"":" and stat_dt <='{$this->et}' ";
		$where = "
				stat_dt >='{$this->bt}'
				$et
		";
		$data = $table->where($where)->order("stat_dt DESC,module_id desc,type_id ASC")->select();
		$result = array();
		foreach ($data as $each){
			$result[$each["stat_dt"]][$each["module_id"].$each["type_id"]] = $each["type_value"];
		}		
		if(!empty($data)){
			foreach ($result as $key=>$each){
				$result[$key]["sum"] = array_sum($each);
				$result[$key]["stat_dt"] = $key;
				foreach ($result[$key] as $k=>$v){
					$sum_sum[$k] += $v;
				}
			}
			$sum_sum["stat_dt"] = "累计";
			$result[] = $sum_sum;
		}
		$data = array_values($result);
		$totalRecords = $table->where($where)->count();
		$table_data["total"] = $totalRecords;
		$table_data["rows"] = empty($data)?array():$data;
		$this->ajaxReturn($table_data);
	}


	
	
}