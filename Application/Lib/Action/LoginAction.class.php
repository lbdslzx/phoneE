<?php

class LoginAction extends CommonAction {
	public function index(){
		session_destroy();
		$this->display();
	}
	public function login(){
		if(IS_POST){
			if($this->check_verify()){
				$table = M("t_admin_info",null,"db_admin");
				$condition = array(
						"login_name" => I("f_user"),
						"password" =>md5( I("f_pwd")),
						);
				$sql = "SELECT * from t_admin_info WHERE login_name = '".I("f_user")."' AND `password` = '".md5( I("f_pwd"))."'";
				$data = $table->query($sql);
				if(empty($data)){
					$this->error("用户名或密码错误",null,1);
				}else{
					$adminInfo['user_name'] = I("f_user");
					$adminInfo['admin_level'] = $data[0]['admin_level']; 
					$adminInfo['login_name'] = $data[0]['login_name'];
					$adminInfo['admin_id'] = $data[0]['admin_id'];
					$adminInfo['admin_name'] = $data[0]['admin_name'];
					$adminInfo['online_time'] = time();
					$this->setLoginSession($adminInfo);
					$this->redirect("Index/index");
				}
			}else{
				$this->error("验证码错误",null,1);
			}
		}else{
			$this->error("非法操作","index");
		}
	}
	function check_verify(){
	    if($_SESSION['verify_code'] != $_POST['f_code']) {
       		$this->error("验证码错误",null,1);
    	}
		return true;
	}
	public function verify(){
		import('@.CustomLib.VerifyCode');
		$class = new VerifyCode();
    	$class->show();
	}
	
	public function logOut(){
		session_destroy();
		$this->redirect("index");
	}
}