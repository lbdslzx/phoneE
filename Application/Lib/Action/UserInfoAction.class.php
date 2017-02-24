<?php

class UserInfoAction extends CommonAction {

	public $table_name = "t_user_config";
	public $db_name = DB_UC;
	
	function __construct(){
		parent::__construct();
		
	}
	public function index(){
// 等待以后开发
	}
	

	public function view(){
		$db = M("t_user_config",null,$this->db_name);
		$user_id = I(2);
		$data = $db->where("user_id = $user_id")->find();
		if(!empty($data)){
			
			$data["gender"] = chn_gender($data["gender"]);
			$data["birthday"] = substr($data["birthday"], 0,4)."-".substr($data["birthday"], 4,2)."-".substr($data["birthday"], -2);
			$data["age"] = $this->getAgeByBirth($data["birthday"]);
		}else{
			$data["user_id"] = "该用户不存在";
		}
		$this->assign($data);
		$db_query = M("t_user_phone",null,DB_QUERY);
		$uid_data = $db_query->where("user_id = $user_id")->find();
		$this->assign($uid_data);
		$this->display();
	}
	/**
	 * 根据出生日期计算年龄
	 * @param unknown $birth 格式 yyyy--mm--dd
	 * @return number
	 */
	public  function getAgeByBirth($birth){
		list($by,$bm,$bd)=explode('-',$birth);
		$cm=date('n');
		$cd=date('j');
		$age=date('Y')-$by;
		if ($bm > date("m")){
			$age--;
		}elseif ($bm == date("m") && $bd > date("d")){
			$age --;
		}
		$age = $age < 0?0:$age;
		return $age;
	}
	
	
}