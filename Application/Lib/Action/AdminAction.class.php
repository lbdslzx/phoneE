<?php

class AdminAction extends CommonAction {
	public function listAdmin(){
		$table_title = array(
			"admin_name"	=>"管理员名",
			"login_name"	=>"登录名",
			"admin_level_chn"	=>"权限",
			"insert_dt"	=>"添加时间",
			"action"	=>"操作提示",
		);
		$this->assign("table_title",$table_title);
		$this->display("admin_rights_list");
	}
	
	public function getAdminList(){
		$page = $_GET["page"];
		$rows = $_GET["rows"];
		$admin_name = $_GET["admin_name"];
		$table = M("t_admin_info",null,"db_admin");
		$where = array("admin_name like '%$admin_name%'");
		$data = $table->field("admin_id,admin_name,admin_level,login_name,limit_login_ip,insert_dt")->where("login_name != 'admin'")->where($where)->page($page,$rows)->order("insert_dt desc")->select();
		$data = empty($data)?array():$data;
		$totalRecords = $table->field("admin_id,admin_name,admin_level,login_name,limit_login_ip,insert_dt")->where("login_name != 'admin'")->where($where)->count();

		foreach ($data as $k=>$v){
			$data[$k]["admin_level_chn"] = $this->perToChn($data[$k]["admin_level"], $this->xml);
			$data[$k]["json_info"] = urlencode(json_encode($data[$k]));
			$data[$k]["action"] = '<a href="editAdmin/?json='.$data[$k]["json_info"].'" class="easyui-linkbutton" iconCls="icon-edit" plain="true">编辑</a>
									<a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-cancel" plain="true" onclick=\' return del('.$data[$k]["admin_id"].');\'>删除</a>';
		}
		$table_data["total"] = $totalRecords;
		$table_data["rows"] = $data;
		$this->ajaxReturn($table_data);
	}
	//编辑管理员
	public function editAdmin(){
		
		if(IS_POST){
			$data = $this->jsonParse($_POST['json']);
			$where = $this->jsonParse($_POST['whjson']);
			$form = M("t_admin_info",null,DB_ADMIN);
			if(empty($data['password'])){
				unset($data['password']);
			}else{
				$data['password'] = md5($data['password']);
			}
			$result = $form->where($where)->save($data);
			$this->ajaxReturn($result);
		}
		
		$json = $_GET["json"];
		if(get_magic_quotes_gpc()){
			$json = stripcslashes($json);//如果开启了php转义，取消转义
		} 
		$json = json_decode($json); //转换为数组
		$json = (array)$json;
		$admin_level = $json["admin_level"];
		$this->assign("admin_level",$admin_level);
		$this->assign($json);
		//动态生成权限html
		$list = $this->_getLevelList();
		$this->assign("html",$list);
		$this->display("admin_rights_edit");
	}
	private function _getLevelList(){
		//动态生成权限html
		$permission = XMLToArray::createArray(file_get_contents($this->xml));
		$list = "<ul style='list-style:none;margin:0;padding:0;'>";
		foreach ($permission['Menu']['SubMenu'] as $subKey => $subValue) {
			$firstNodeText = $subValue['Name'];
			$firstNodeId = $subValue['Permission'];
			$Module = $subValue['Module'];
			$Module = is_array($Module) ? (isset($Module[0]) ? $Module : [$Module]) : [];
			$RoleModule = $subValue['RoleModule'];
			$RoleModule = is_array($RoleModule) ? (isset($RoleModule[0]) ? $RoleModule : [$RoleModule]) : [];
			$module = array_merge($RoleModule,$Module);
			if($firstNodeId != 'SP'){
				$list .= "<li><img id='img_{$firstNodeId}' src='' style='margin: 0 2px 0 0;'/>";
				$list .= "<input type='checkbox' class='inputClass upLayer' name='{$firstNodeId}' id='{$firstNodeId}' />";
				$list .= $firstNodeText;
				$list .= "<ul id='ul_{$firstNodeId}' style='display:none;list-style:none;margin:0 0 0 15px;padding:0;'>";
				foreach ($module as $key => $value){
					$secondNodeText = $value['Name'];
					$secondNodeId = $value['Permission'];
					$Item = @$value['Item'];
					$data = @$value['Data'];
					$role = @$value['Role'];
					if(is_array($Item) || is_array($data) || is_array($role)){
						$list.="<li><img id='img_{$secondNodeId}' src='' style='margin:0 2px 0 0;'>";
						$list .= "<input type='checkbox' class='inputClass {$firstNodeId}' name='{$secondNodeId}'  id='{$secondNodeId}'>";
						$list .= $secondNodeText;
						$list .= "<ul id='ul_{$secondNodeId}' style='display:none;list-style:none;margin:0 0 0 35px;padding:0;'>";
						if(is_array($data)){
							$table = M($data['Table'],null,$data['DataBase']);
							$where = $data['Where'] ? $data['Where'] : "1=1";
							$result = $table->where($where)->field([$data['Permission'],$data['Name']])->select();
							$identifying = $data['Identifying'];
							foreach ($result as $_data){
								$thirdNodeText = $_data[$data['Name']];
								$thirdNodeId = $secondNodeId."-".$identifying."-".$_data[$data['Permission']];
								$list .= "<li><input type='checkbox' class='inputClass {$secondNodeId}'  name='{$thirdNodeId}'  id='{$thirdNodeId}'>";
								$list .= $thirdNodeText;
								$list .= "</li>";
							}
						}
						if(is_array($Item)) {
							foreach ($Item as $_item) {
								$thirdNodeText = $_item['Name'];
								$thirdNodeId = $_item['Permission'];
								$list .= "<li><input type='checkbox' class='inputClass {$secondNodeId}'  name='{$thirdNodeId}'  id='{$thirdNodeId}'>";
								$list .= $thirdNodeText;
								$list .= "</li>";
							}
						}
						if(is_array($role)){
							$role = isset($role[0]) ? $role : [$role];
							foreach ($role as $_role){
								$thirdNodeText = $_role['Name'];
								$thirdNodeId = $secondNodeId."-role-".$_role['Permission'];
								$list .= "<li><input type='checkbox' class='inputClass {$secondNodeId}'  name='{$thirdNodeId}'  id='{$thirdNodeId}'>";
								$list .= $thirdNodeText;
								$list .= "</li>";
							}
						}
						$list .= "</ul></li>";
					}else{
						$list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' class='inputClass {$firstNodeId}' name='{$secondNodeId}' id='{$secondNodeId}'>";
						$list .= $secondNodeText;
						$list .= "<ul id='ul_{$secondNodeId}' style='display:none;list-style:none;margin:0 0 0 15px;padding:0;'></ul></li>";
					}
				}
				$list .= "</ul>";
				$list .="</li>";
			}
		}
		$list .= "</ul>";
		return $list;
	}
	//删除管理员
	public function del(){
		$table = M("t_admin_info",null,DB_ADMIN);
		$where = array(
				"admin_id" 	=> $_POST["id"],
				);
		$result = $table->where($where)->delete();
		$this->ajaxReturn($result);		
	}
	
	public function Add(){
		if(IS_POST){  //添加管理员
			$table = M("t_admin_info",null,DB_ADMIN);
			$json =$this->jsonParse($_POST['json']);
			$json["insert_dt"] = date("Y-m-d H:i:s");
			$result = $table->add($json);
			if($result){
				die("操作成功");
			}else{
				die("操作失败");
			}
		}
		//动态生成权限html
		$this->assign("html",$this->_getLevelList());
		$this->display("admin_rights_add");
	}
	
	/*
	 * 将权限转化成中文
	 * parm:xml路径 
	 */
	public function perToChn($str,$xml){
		$Permission = XMLToArray::createArray(file_get_contents($xml));
		foreach ($Permission['Menu']['SubMenu'] as $subKey => $subValue) {
			$permission[] = $subValue['Permission'];
			$name[] = $subValue['Name'];
			if(!isset($subValue['Module'][0])){
				$subValue['Module'] = [$subValue['Module']];
			}
			$module = isset($subValue['RoleModule']) && is_array($subValue['RoleModule']) ? array_merge($subValue['Module'],@$subValue['RoleModule']) : $subValue['Module'];
			foreach ($module as $value){
				$permission[] = $value['Permission'];
				$name[] = $value['Name'];
				if(!empty($value['Item'])){
					$value['Item'] = isset($value['Item'][0]) ? $value['Item'] : [$value['Item']];
					foreach ($value['Item'] as $item){
						$name[] = $item['Name'];
						$permission[] = $item['Permission'];
					}
				}
				$data = $value['Data'];
				if(is_array($data)){
					$table = M($data['Table'],null,$data['DataBase']);
					$result = $table->field([$data['Permission'],$data['Name']])->select();
					$identifying = $data['Identifying'];
					foreach ($result as $_data){
						$name[] = $value['Name'].";".$_data[$data['Name']];
						$permission[] = $value['Permission']."-".$identifying."-".$_data[$data['Permission']];
					}
				}
				$role = $value['Role'];
				if(is_array($role)){
					$role = isset($role[0]) ? $role : [$role];
					foreach ($role as $roleItem){
						$permission[] = $value['Permission']."-role-".$roleItem['Permission'];
						$name[] = $value['Name']."-".$roleItem['Name'];
					}
				}
			}
		}
		$_str = "";
		$arrStr = explode(" ",$str);
		if(in_array('A',$arrStr)){
			return "[超级管理员]";
		}
		foreach ($arrStr as $str){
			if(!is_null($str)){
				$key = array_search($str,$permission);
				$_str = $key ? "{$_str}[{$name[$key]}]" : $_str;
			}
		}
		return $_str;
	}
}