<?php

class BugsAndroidManageAction extends CommonAction {

	public $table_name = "t_log_android_bugs";
	public $db_name = DB_BUG;
	public $primary_key = "bug_id"; //主键
	function __construct(){
		parent::__construct();
		
	}
	public function index(){
		$table_title = array(
			"server_ip"		=>"服务器",
			  "bug_id"		=>"bug编号",
			  "user_id"		=>"用户ID",
			  "app_version_code"		=>"客户端版本",
			  "operate_version"		=>"操作系统版本",
			  "phone_product"		=>"手机生产商",
			  "report_date"		=>"报告时间",
			  "phone_model"		=>"设备型号",
//			  "op_status"		=>"处理状态",
			  "action"			=>"操作提示"
		);
		$this->assign("table_title",$table_title);
// 		$table = M($this->table_name,null,$this->db_name);
// 		$server_ip = $table->field("distinct server_ip")->select();
// 		foreach ($server_ip as $key=>$val){
// 			$server_ip[$key]["server_ip"] = trim($val["server_ip"]);
// 		}
		$server_ip = array(
				"bj.test.health.langma.cn"		=>"北京测试服",
				"entry.health.langma.cn"		=>"北京正式服",
		);
		$this->assign("server",$server_ip);

		$this->display();
	}
	public function chn_op_status($type){
		$_result = $type;
	    switch($_op_status){
	        case '0':
	            $_result = "未接收";
	            break;
	        case '1':
	            $_result = "已接收";
	            break;
	        case '2':
	            $_result = "已处理";
	            break;
	        default:
	            $_result = "未接收";
	            break;
	    }
	    return $_result;
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
		$data = $table->where($where)->page($this->page,$this->rows)->order("report_date DESC")->select();
		$totalRecords = $table->where($where)->count();
		foreach ($data as $k=>$v){
			$data[$k]["op_status"] = $this->chn_op_status(($data[$k]["op_status"]));
			$data[$k]["action"] = '<a href="javascript:void(0);" onclick="view('.$data[$k]["bug_id"].');" class="easyui-linkbutton" iconCls="icon-cancel" plain="true">查看详细</a>';
// 			$data[$k]["action"] = '
// 					<a href="edit/?id='.$data[$k]["word_id"].'" class="easyui-linkbutton" iconCls="icon-edit" plain="true">编辑</a>
// 					<a href="javascript:void(0);" onclick="del('.$data[$k]["word_id"].');" class="easyui-linkbutton" iconCls="icon-cancel" plain="true">删除</a>
// 							';
		}
		$table_data["total"] = $totalRecords;
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