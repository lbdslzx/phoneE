<?php
/**
 * 用户激活数
 * @author Administrator
 *
 */
class StatEnabledAction extends CommonAction {

public $table_name = "t_stat_daily_reg_report";
	public $db_name = DB_STAT;
	public $primary_key = "stat_dt"; //主键
	function __construct(){
		parent::__construct();
	}
	public function index(){
		$table_title = array(
			  "stat_dt"		=>"统计时间",
			  "1"		=>"已激活",
			  "2"		=>"未激活",
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
		$where = " $c
				module_id  = 5
				and stat_dt >='{$this->bt}'
				$et
		";
		$data = $table->where($where)->order("stat_dt DESC,module_id desc,type_id ASC")->select();
		$result = array();
		foreach ($data as $each){
			$result[$each["stat_dt"]]["stat_dt"] = $each["stat_dt"];
			$result[$each["stat_dt"]][$each["type_id"]] = $each["type_value"];
			$result[$each["stat_dt"]]["sum"] = $result[$each["stat_dt"]]["1"]+$result[$each["stat_dt"]]["2"];
		}
		
		if(!empty($data)){
			foreach ($result as $each){
				$androi_sum += $each["1"];
				$ios_sum += $each["2"];
				$sum_sum += $each["sum"];
			}
			$result[] = array(
					"stat_dt" => "累计",
					"1" => $androi_sum,
					"2" => $ios_sum,
					"sum" => $sum_sum,
			);
		}
// 		print_r($result);exit;
		$data = array_values($result);
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