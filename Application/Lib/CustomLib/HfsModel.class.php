<?php 

class HfsModel{
	
	const ACTION_TYPE_UPLOAD = 1;
	const ACTION_TYPE_DOWN = 2;
	const ACTION_TYPE_MOVE = 3;
	const ACTION_TYPE_DELETE = 4;
	const OP_TYPE_DEVICE_PIC  = 3002;
	const OP_TYPE_IVR_FILE  = 3006;
	const OP_TYPE_IVR_FILE_DOWN  = 3007;
	const OP_TYPE_INFORMATION_IMG = 3008;
	const OP_TYPE_YD_IMG = 3009;
	const OP_TYPE_ID_CARD = 3011;

	public $parms = array();//需要发送的参数
	public $hfs_addr;
	public $op_type;
	public $file_name;
	public $pid = 5;
	function __construct($op_type,$file_name ="",$admin_id=""){
		
		$this->addParm("op_type",$op_type);
		$this->addParm("user_id",$admin_id);
		$this->addParm("pid","5");
		$this->addParm("act_type",self::ACTION_TYPE_UPLOAD);
		$this->addParm("file_name",$file_name);
		$this->addParm("gender","0");
		$this->addParm("sid","admin_sid");
		$this->addParm("c_ver","0.1");
		$this->addParm("c_type","1");
		$this->addParm("task_id","admin_task");
		$this->hfs_addr = formateURIAddr(C("hfs_addr"));
		$this->op_type = $op_type;
		$this->file_name = $file_name;
	}
	
	public function addParm($key,$val){
		$this->parms[$key] = $val;
	}
	
	public function getJson(){
		return json_encode($this->parms);
	}
	
	public function getDownUrl($file_name){
		$url = $this->hfs_addr.$this->op_type."/{$this->pid}/".$file_name;
		return $url;
	}
	
	/**
	 * 获取需要上传图片地址
	 * @param array $params 下载地址需要组装的参数
	 */
	public function getUploadAddr($params){
		$addr = $this->hfs_addr;
		$params["op_type"] = $this->op_type;
		$params["act_type"] = self::ACTION_TYPE_UPLOAD;
		$params = array_merge($this->parms,$params);
		if (empty($params["file_name"])){
			$params["file_name"] = getMillisecond().".jpg";
		}
		$addr .= "?json=".json_encode($params);
		return $addr;
	}
}
