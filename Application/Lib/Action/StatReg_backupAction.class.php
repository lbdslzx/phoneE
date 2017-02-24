<?php

class StatReg_backupAction extends CommonAction {

	public $table_name = "t_stat_daily_reg_report";
	public $db_name = DB_STAT;
	public $primary_key = "stat_dt"; //主键
	function __construct(){
		parent::__construct();
		
	}
	public function index(){
		$table_title = array(
			"stat_dt"		=>"统计时间",
			  "11"		=>"新增注册成功数",
			  "21"		=>"用户ID",
			  "22"		=>"客户端版本",
			  "31"		=>"操作系统版本",
			  "32"		=>"手机生产商",
			  "41"		=>"报告时间",
			  "42"		=>"设备型号",
				"43"		=>"设备型号",
				"44"		=>"设备型号",
				"51"		=>"设备型号",
				"53"		=>"设备型号",
				"54"		=>"设备型号",
				"56"		=>"设备型号",
				"57"		=>"设备型号",
				"58"		=>"设备型号",
				"59"		=>"设备型号",
				"511"		=>"设备型号",
				
//			  "op_status"		=>"处理状态",
// 			  "action"			=>"操作提示"
		);
		$this->assign("table_title",$table_title);
		$table = M($this->table_name,null,$this->db_name);
		$title = $table->field("module_id,module_name,type_id,type_name")
				->group("module_id,type_id")
				->select();
// 		$html = "";
		foreach ($title as $each){
			$html[0][$each["module_id"]] = $each["module_name"];
			$html[1][$each["module_id"].$each["type_id"]] = $each["type_name"];

// 			$html["module_id"]
		}
// 		print_r($html);exit;
		$this->assign("server",$server_ip);
		$this->display();
	}
	
	public function getList(){
		parent::getList();
		$table = M($this->table_name,null,$this->db_name);	
		$this->end_dt = empty($this->end_dt)?date("Y-m-d"):$this->end_dt;
		$this->end_dt = $this->end_dt." 23:59:59";
		$where = "user_id like '%{$this->user_id}%' 
				and report_date >= '{$this->begin_dt}'
				and report_date <= '{$this->end_dt}'
				and app_version_code like '%{$this->app_version_code}%'
				and phone_product like '%{$this->phone_product}%'
				and phone_model like '%{$this->phone_model}%'
				and server_ip like '%{$this->server_ip}%'
		";
		$dataset = $table->where()->order("stat_dt,module_id,type_id DESC")->select();

// 		$totalRecords = $table->where($where)->count();
		$data = array();
		foreach ($dataset as $each){
// 			print_r($each);exit;
			$data[$each["stat_dt"]]["stat_dt"] = $each["stat_dt"];
			$data[$each["stat_dt"]][$each["module_id"].$each["type_id"]] = $each["type_value"];
			$data[$each["stat_dt"]]["other"] = '<a href="javascript:void(0);" onclick="view();" class="easyui-linkbutton" iconCls="icon-cancel" plain="true">删除</a>';
		}
		$data = array_values($data);
// 		print_r($data);exit;
		
		$table_data["total"] = 50;
		$table_data["rows"] = empty($data)?array():$data;
		$this->ajaxReturn($table_data);
	}
	//查看
	public function view(){
		$db = M($this->table_name,null,$this->db_name);
		
		$title ="查看";
		$where = array(
			$this->primary_key =>	$_GET["id"]
		);
		$data  = $db->where($where)->select();
		$data = $data[0];
		$this->assign($data);
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