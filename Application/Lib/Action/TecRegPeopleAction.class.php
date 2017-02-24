<?php

class TecRegPeopleAction extends CommonAction {

	public $table_name = "t_log_user_reg_count";
	public $db_name = DB_LOG_USER;
	
	function __construct(){
		parent::__construct();
		
	}
	public function index(){
		$table_title = array(
				"reg_num"	=>"注册数",
				"active_num"	=>"激活数",
				"log_dt"	=>"日志时间",
		);
		$this->assign("table_title",$table_title);
		$this->display();
	}
	public function getList(){
		parent::getList();
		$table = M($this->table_name,null,$this->db_name);		
		$begin_dt = I("begin_dt");
		$end_dt = I("end_dt");
		if (empty($begin_dt)){
			$begin_dt = "1000-02-02 10:01:01";
		}
		if (empty($end_dt)){
			$end_dt = "2099-02-02 10:01:01";
		}
		$where = "log_dt >= '$begin_dt' and log_dt <= '$end_dt'";
		$data = $table->where($where)->page($this->page,$this->rows)->order("log_dt DESC")->select();
		$totalRecords = $table->where($where)->count();
		$table_data["total"] = $totalRecords;
		$table_data["rows"] = empty($data)?array():$data;
		$this->ajaxReturn($table_data);
	}
	//编辑
	public function edit(){
		$db = M($this->table_name,null,DB_SYS);
		if(IS_POST){
			$json = $_POST["json"];
			$data = $this->jsonParse($json);
			$result = $db->data($data)->save();
			$this->ajaxReturn($result);
		}
		$title ="热词编辑";
		$where = array(
			"word_id" =>	$_GET["id"]
		);
		$data  = $db->where($where)->select();
		$data = $data[0];
		$edit_data = json_encode($data);
		$this->assign("data",$edit_data);
		$this->assign("title",$title);
		$this->display();
		
	}
	//删除
	public function del(){
		$table = M($this->table_name,null,DB_SYS);
		$id = $_POST["id"];
		$where = array(
				"word_id" 	=> $id,
				);
		$result = $table->where($where)->delete();
		flushMemcache();
		$this->ajaxReturn($result);
	}
	
	public function Add(){
		if(IS_POST){
			$json = $_POST["json"];
			$data = $this->jsonParse($json);
			$data["insert_dt"] = date("Y-m-d H:i:s");			
			$db = M($this->table_name,null,DB_SYS);
			$result = $db->add($data);
			flushMemcache();
			$this->ajaxReturn($result);
		}
		$title ="热词配置添加";
		$this->assign("title",$title);
		$this->display();
		
	}
	
	
	
}