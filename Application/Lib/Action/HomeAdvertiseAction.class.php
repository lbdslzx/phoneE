<?php

class HomeAdvertiseAction extends CommonAction {

	public $table_name = "t_app_index_advert_cfg";
	public $db_name = DB_QUERY;
	public $primary_key = "id";
	public $forward_type = array(//跳转类型
		"1"		=> "打开URL",
		"2"		=> "打开APP功能模块",
	);
	public $all_publish_state = array(//发布状态
			"1"		=> "已发布",
			"2"		=> "待发布",
			"3"		=> "已过期",
	);
	public $all_forward_module = array( //app内部跳转模块
		"0"	=> "无",
	);
	function __construct(){
		parent::__construct();
		$this->assign("forward_type",$this->forward_type);
		$this->assign("all_publish_state",$this->all_publish_state);
		$this->assign("all_forward_module",$this->all_forward_module);
	}
	public function index(){
		$table_title = array(
			 "type"      =>"跳转类型",
			 "open_url"      =>"跳转url",
			 "app_module"      =>"跳转模块",
			 "describe"      =>"描述",
			 "publish_state"      =>"发布状态",
//  			 "oper_id"      =>"操作员id",
			 "insert_dt"      =>"添加时间",
			"action"      =>"操作提示",
		);
		$this->assign("table_title",$table_title);
		$this->display();
	}
	public function getList(){
		parent::getList();
		$table = M($this->table_name,null,$this->db_name);
		$where = "publish_state like '%$this->publish_state%' and type like '%$this->type%'";

		$data = $table->where($where)->page($this->page,$this->rows)->order("id asc")->select();
		
		foreach ($data as $k=>$v){
			$data[$k]["type"] = chn_flag($this->forward_type, $data[$k]["type"]);
			$data[$k]["app_module"] = chn_flag($this->all_forward_module, $data[$k]["app_module"]);
			$data[$k]["publish_state"] = chn_flag($this->all_publish_state, $data[$k]["publish_state"]);
			
			$data[$k]["action"] = '
					<a href="edit/?'.$this->primary_key.'='.$data[$k][$this->primary_key].'" class="easyui-linkbutton" iconCls="icon-edit" plain="true">编辑</a>
							';
// 					<a href="javascript:void(0);" onclick="del('.$data[$k]["id"].');" class="easyui-linkbutton" iconCls="icon-cancel" plain="true">删除</a>			

		}
		$totalRecords = $table->where($where)->count();
		$table_data["total"] = $totalRecords;
		$table_data["rows"] = empty($data)?array():$data;
		$this->ajaxReturn($table_data);
	}
	//编辑
	public function edit(){
		$db = M($this->table_name,null,$this->db_name);
		if(IS_POST){
			$this->log_trace(print_r($_POST,true));
			$data = $_POST;
// 			$data["insert_dt"] = date("Y-m-d H:i:s");
			$data["oper_id"] = $this->getCurrentAdminId();
			$result = $db->save($data);
			flushMemcache();
			$this->ajaxReturn($result);
		}
		$title ="编辑";
		$where = array(
			"$this->primary_key" =>	$_GET[$this->primary_key],
		);
		$data  = $db->where($where)->find();
		$edit_data = json_encode($data);
		$this->assign("data",$edit_data);
		$this->assign("title",$title);
		$this->display();

		
	}

	public function Add(){
		if(IS_POST){
			$db = M($this->table_name,null,$this->db_name);
			$data = $_POST;
			$data["insert_dt"] = date("Y-m-d H:i:s");
			$data["oper_id"] = $this->getCurrentAdminId();
			$this->log_trace(print_r($data,true));			
			$result = $db->add($data);
			flushMemcache();
			$this->ajaxReturn($result);
		}
		$title ="添加";
		$this->assign("title",$title);
		$this->display();
		
	}
	
	
	
}