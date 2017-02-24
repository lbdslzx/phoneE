<?php

class StatPharmacyRankAction extends CommonAction {

public $table_name = "t_stat_daily_pharmacy_report";
	public $db_name = DB_STAT;
	public $primary_key = "stat_dt"; //主键
	function __construct(){
		parent::__construct();
	}
	public function index(){
		$table_title = array (
				"stat_dt" => "统计时间",
				"type_name" => "药店名",
// 				"module_name" => "统计类型",
				"type_value" => "次数",
		);
		$this->assign("table_title",$table_title);
// 		$table = M($this->table_name,null,$this->db_name);
// 		$item = $table->field("DISTINCT module_id,module_name")->order("module_id")->where("module_id > 5")->select();
		$item = array(
			"1"		=> "血压",
			"2"		=> "体重",
					
		);
		$rank_type = array(
// 			"1"		=>"日排行榜",
			"2"		=>"周排行榜",
// 			"3"		=>"月排行榜"
		);
		$this->assign("item",$item);
		$this->assign("rank_type",$rank_type);
		$this->display();
	}
	public function getList(){
		parent::getList();
		$module_id = 0; 
		$this->rank_type = 2;
		if ($this->mea_type == 1 ){
			if ( $this->rank_type == 1){
				$module_id = 7;
			}elseif ($this->rank_type == 2){
				$module_id = 10;
			}elseif ($this->rank_type == 3){
				$module_id = 13;
			}
		}elseif ($this->mea_type == 2 ){
			if ( $this->rank_type == 1){
				$module_id = 6;
			}elseif ($this->rank_type == 2){
				$module_id = 9;
			}elseif ($this->rank_type == 3){
				$module_id = 12;
			}
		}
		if(empty($module_id)) return ;
		$table = M($this->table_name,null,$this->db_name);	
		$et = empty($this->et)?"":" and stat_dt <='{$this->et}' ";
		$where = " $c
		module_id  = {$module_id} 
		and stat_dt >='{$this->bt}'
		$et
		";
		$data = $table->where($where)->order("stat_dt desc,module_id ASC ,type_value desc")->select();
		if (!empty($data)){
			foreach ($data as $each){
				foreach ($each as $k=>$val){
					$sum_sum[$k] += $val;
				}
			}
			$sum_sum["stat_dt"] = "累计";
			$sum_sum["type_name"] = "";
			array_push($data, $sum_sum);
		}
		$totalRecords = $table->where($where)->count();
		$table_data["total"] = $totalRecords;
		$table_data["rows"] = empty($data)?array():$data;
		$this->ajaxReturn($table_data);
	}

	
	
	
	
}