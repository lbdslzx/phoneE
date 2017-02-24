<?php

class SmsSendAction extends CommonAction {

	
	function __construct(){
		parent::__construct();
	}
	public function index(){
		
		$this->display();
	}

	public function send(){
		$mobile = $_POST["mobile"];
		if(!preg_match("/\d{11}$/", $mobile)){
			$this->ajaxReturn(-2);
		}
		if(empty($mobile)){
			$this->ajaxReturn(-1);
		}
		$text =C("sms_text");
		$api_addr = formateURIAddr(C("sms_cfg.api_addr"));
		$arr = array();
		$arr["app_id"] = C("sms_cfg.app_id");
		$arr["op_type"] = "1001";
		$arr["phone"] = $mobile;
		$arr["sms"] = $text;
		ksort($arr);
		$arrStr  = http_build_query($arr);
		$sign = md5($arrStr);
		$arr["sign"] = $sign;
		$addr = $api_addr."?json=".json_encode($arr);
		$ret = file_get_contents($addr);
		$ret = json_decode($ret);
		$code = $ret->code;
		$this->ajaxReturn($code);
	}
	
}