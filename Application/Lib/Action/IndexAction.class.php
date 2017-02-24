<?php

class IndexAction extends CommonAction {
	public function index(){
		if(!$this->isLogin()){
			$this->redirect("Login/index");
		}
		$this->assign("user_name",$this->getLoginParam('user_name'));
		$this->display();
	}
	public function createMenu(){
		$menu_html = $this->menuInit();
		$this->assign("menu_html",$menu_html);
		$this->display("menu");
	}
	/*
	 * 显示指定模板
	 */
	public function showTpl(){
		$this->assign("user_name",$this->getLoginParam('user_name'));
		$this->display($this->_param(2));
	}
	
	/*
	 * 修改密码
	 */
	public function changePwd(){
		$db = M("t_admin_info",null,DB_ADMIN);
		$json = $this->jsonParse($_POST['json']);
		$sql = "   call pr_admin_change_password(".$this->getCurrentAdminId().",'".md5($json['old_password'])."','".md5($json['again_password'])."')";
		$result = $db->query($sql);
		if($result[0][0] == "0"){
			$this->ajaxReturn("操作成功","EVAL");
		}else{
			$this->ajaxReturn("操作失败,原密码错误","EVAL");
		}
	}
	/*
	 * 根据用户权限动态生成用户菜单
	 * author ysg
	 */
	public function generateMenuItem($menuNode){
		if(is_array($menuNode) && !empty($menuNode)){
			if(isset($menuNode['Url']) && !empty($menuNode['Url'])){
				if(isset($menuNode['Module']) && is_array($menuNode['Module'])){
					$list = "<li><a id='{$menuNode['Permission']}' href='{$menuNode['Url']}'>{$menuNode['Name']}</a></li>";
				}else{
					$list = "<li><a class='item' onclick='return add(this);' id='{$menuNode['Permission']}' href='{$menuNode['Url']}'>{$menuNode['Name']}</a></li>";
				}
			}else{
				$list = "<li class='submenu'>";
				$list .= "<a class='menu-sty2' id='{$menuNode['Permission']}'>{$menuNode['Name']}</a>";
				$list .= "<ul class='submenu'>";
				if(is_array($menuNode['Module'])) {
					$module = isset($menuNode['Module'][0]) ? $menuNode['Module'] :[$menuNode['Module']];
					foreach ($module as $value) {
						$list .= $this->generateMenuItem($value);
					}
				}
				$list .="</ul>";
				$list .="</li>";
			}
		}
		return $list;
	}
		//生成菜单数据
		public function menuInit(){
			$list = "<ul class='menu-sty'>";
			$permission = XMLToArray::createArray(file_get_contents($this->xml));
			$adminLevel=$this->getLoginParam('admin_level');
			foreach ($permission['Menu']['SubMenu'] as $subKey => $subValue){
				if(preg_match("/\bA\b/",$adminLevel)
					|| preg_match("/\b".$subValue['Permission']."\b/",$adminLevel)){
					$list .= $this->generateMenuItem($subValue);
				}elseif (!(preg_match("/\b".$subValue['Permission']."\b/",$adminLevel)
					|| preg_match("/\bA\b/",$adminLevel))){
					$hasSubMenu=false;
					$module = isset($subValue['Module'][0]) ? $subValue['Module'] :[$subValue['Module']];
					foreach ($module as $value){
						if(preg_match("/\b".$value['Permission']."\b/",$adminLevel)){
							if(!$hasSubMenu){
								$list .= "<li class='submenu'>";
								$list .= "<a class='menu-sty2' id='{$subValue['Permission']}'>{$subValue['Name']}</a>";
								$list .= "<ul class='submenu'>";
								$hasSubMenu = true;
							}
							$list .= $this->generateMenuItem($value);
						}
					}
					if($hasSubMenu){
						$list.="</ul></li>";
					}
				}
			}
			$list .="</ul>";
			return $list;
		}	
	
	
}