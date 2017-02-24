<?php

class DevicePicManageAction extends CommonAction {

	public $table_name = "t_sys_support_device";
	public $db_name = DB_SYS;
	public $primary_key = "support_id";
	function __construct(){
		parent::__construct();
	}
	public function index(){
		$table_title = array(
//			 "device_id"      =>"设备ID",
			 "device_name"      =>"设备名称",
// 			 "device_desc"      =>"设备描述",
			 "bluetooth_name"      =>"蓝牙名",
			 "bluetooth_password"      =>"蓝牙密码",
// 			 "UUID"      =>"UUID",
			 "begin_version"      =>"起始版本",
			 "end_version"      =>"结束版本",
// 			 "device_type"      =>"设备类型",
 			 "device_icon"      =>"设备图片",
			 "phone_os"      =>"操作系统",
			 "is_official"      =>"是否官方",
// 			 "buy_url"      =>"购买地址",
// 			 "insert_dt"      =>"添加时间",
			"action"      =>"操作提示",
		);
		$this->assign("table_title",$table_title);
		$db = M("t_sys_disease_cfg",null,DB_SYS);
		$f_cat = $db->where("father_id = 0")->select();
		$this->assign("f_cat",$f_cat);
		$this->display();
	}
	public function getList(){
		parent::getList();
		$table = M($this->table_name,null,$this->db_name);
		if (!empty($this->version)){
			$cond = "
					AND begin_version  <='{$this->version}'
		  			AND end_version >= '{$this->version}'
					";
		}
		$where = "device_name like '%{$this->device_name}%'	$cond	" ;
		$data = $table->where($where)->page($this->page,$this->rows)->order("begin_version desc,device_id asc")->select();
		$fs = new HfsModel(HfsModel::OP_TYPE_DEVICE_PIC);
		
		foreach ($data as $k=>$v){
			$data[$k]["is_official"] = chn_is($data[$k]["is_official"]);
			$data[$k]["phone_os"] = chn_phone_os($data[$k]["phone_os"]);
			
			$data[$k]["action"] = '
					<a href="edit/?'.$this->primary_key.'='.$data[$k][$this->primary_key].'" class="easyui-linkbutton" iconCls="icon-edit" plain="true">编辑</a>
							';
// 					<a href="javascript:void(0);" onclick="del('.$data[$k]["id"].');" class="easyui-linkbutton" iconCls="icon-cancel" plain="true">删除</a>			
			$pic_addr = $fs->getDownUrl($data[$k]["device_icon"]); 
			
			$data[$k]["device_icon"] = "<img alt='找不到图片' title='点击查看大图' src='".$pic_addr."' width='50px' onclick='view_pic(\"".$pic_addr."\");' />"; 
			
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
			$data["insert_dt"] = date("Y-m-d H:i:s");
			$result = $db->save($data);
			flushMemcache();
			$this->ajaxReturn($result);
		}
		$title ="编辑";
		$where = array(
			"support_id" =>	$_GET["support_id"],
		);
		$data  = $db->where($where)->find();
		$edit_data = json_encode($data);
		$this->assign("data",$edit_data);
		$this->assign("title",$title);
		$hfs = new HfsModel(HfsModel::OP_TYPE_DEVICE_PIC);
		$hfs->addParm("file_name", getMillisecond().".jpg");
		$upload_addr = $hfs->getJson();		
		$down_addr = $hfs->getDownUrl();
		$upload_addr = __URL__."/uploadPic?json=".$upload_addr;
		$this->assign("upload_addr",$upload_addr);
		$this->assign("down_addr",$down_addr);
		$device_type = chn_device_id("", 1);
		unset($device_type[0]);
		$this->assign("device_type",$device_type);
		$pic_addr = $hfs->getDownUrl($data["device_icon"]);
		$this->assign("pic_addr",$pic_addr);
		flushMemcache();
		$this->display();
	}

	public function Add(){
		if(IS_POST){
			$db = M($this->table_name,null,$this->db_name);
			$data = $_POST;
			$data["insert_dt"] = date("Y-m-d H:i:s");
			$data["device_id"] = time();
			$this->log_trace(print_r($data,true));			
			$result = $db->add($data);
			flushMemcache();
			$this->ajaxReturn($result);
		}
		$title ="添加";
		$this->assign("title",$title);
		$hfs = new HfsModel(HfsModel::OP_TYPE_DEVICE_PIC);
		$hfs->addParm("file_name", getMillisecond().".jpg");
		$upload_addr = $hfs->getJson();		
		$down_addr = $hfs->getDownUrl();
		$upload_addr = __URL__."/uploadPic?json=".$upload_addr;
		$this->assign("upload_addr",$upload_addr);
		$this->assign("down_addr",$down_addr);
		$device_type = chn_device_id("", 1);
		unset($device_type[0]);
		$this->assign("device_type",$device_type);
		flushMemcache();
		$this->display();
		
	}
	
	public function uploadPic(){
		import("@.CustomLib.FileClass");
		$stream = file_get_contents($_FILES["file"]["tmp_name"]);
		$json = $_GET["json"];
		$json = $this->jsonParse($json);
		$file_name = $json["file_name"];
		$addr = C("devic_pic");
		$addr = formateURIAddr($addr);
		$addr .= $file_name;
		$this->log_trace("addr:".print_r($addr,true));
		$ret = FileClass::streamUpload($stream, $addr);
		$data = array(
			"file_name"		=>$file_name,
			"code"			=>$ret,
		);
		header('Content-Type: text/html'); //纯文本格式
		echo json_encode($data);
// 		$this->ajaxReturn($data,"json");
	}
	
	
}