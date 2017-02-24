<?php

class PharmacyManAction extends CommonAction {

	public $table_name = "t_pharmacy_info";
	public $db_name = DB_QUERY;
	function __construct(){
		parent::__construct();
	}
	public function index(){
		$table_title = array(
				"pharmacy_id"	=>"登陆ID",
				"pharmacy_name"	=>"药店名称",
				"brand_name"	=>"所属品牌",
				"is_delete"	=>"是否已删除",
				"insert_dt"	=>"添加时间",
				"action"	=>"操作提示",
		);
		$this->assign("table_title",$table_title);
		$this->display();
	}
	public function getList(){
		parent::getList();
		$table = M($this->table_name,null,$this->db_name);		
		$where = "brand_name like '%{$this->brand_name}%' and pharmacy_name  like '%{$this->pharmacy_name}%'";
		$data = $table->where($where)->page($this->page,$this->rows)->order("insert_dt ASC")->select();
		$totalRecords = $table->where($where)->count();
		foreach ($data as $k=>$v){
			$data[$k]["is_delete"] = chn_is($data[$k]["is_delete"]);
			$data[$k]["action"] = '
					<a href="edit/?id='.$data[$k]["pharmacy_id"].'" class="easyui-linkbutton" iconCls="icon-edit" plain="true">编辑</a>
							';
// 			<a href="javascript:void(0);" onclick="del('.$data[$k]["pharmacy_id"].');" class="easyui-linkbutton" iconCls="icon-cancel" plain="true">删除</a>
		}
		$table_data["total"] = $totalRecords;
		$table_data["rows"] = empty($data)?array():$data;
		$this->ajaxReturn($table_data);
	}
	//编辑
	public function edit(){
		$db = M($this->table_name,null,$this->db_name);
		if(IS_POST){
			if(!empty($_POST["ad_password"])){
				$sql_1 = ",admin_password='".md5($_POST["ad_password"])."'";
			}
			if(!empty($_POST["phy_password"])){
				$sql_2 = ",pharmacy_password='".md5($_POST["phy_password"])."'";
			}
			$this->log_trace(print_r($_POST,TRUE));
			$sql = "UPDATE t_pharmacy_info SET 
					pharmacy_name='".$_POST['pharmacy_name']."' 
					$sql_1 $sql_2
					, brand_id='".$_POST['brand_id']."' 
					, brand_name= '".$_POST['brand_name']."' 
					WHERE pharmacy_id = '".$_POST['pharmacy_id']."'";
			$result = $db->execute($sql);
			$this->ajaxReturn($result);
		}
		$title ="编辑";
		$where = array(
			"pharmacy_id" =>	$_GET["id"]
		);
		$data  = $db->where($where)->select();
		$data = $data[0];
		$edit_data = json_encode($data);
		$this->assign("data",$edit_data);
		$this->assign("title",$title);
		//品牌信息
		$table_brand = M("t_pharmacy_brand_info",null,$this->db_name);
		$data = $table_brand->select();
		$this->assign("brand_info",$data);
		$this->display();
		
	}
	//删除
	public function del(){
		$table = M($this->table_name,null,$this->db_name);
		$id = $_POST["id"];
		$where = array(
				"pharmacy_id" 	=> $id,
				);
		$result = $table->where($where)->delete();
		flushMemcache();
		$this->ajaxReturn($result);
	}
	
	public function Add(){
		
		if(IS_POST){
			$db = M($this->table_name,null,$this->db_name);
			$data = $_POST;
			$data["insert_dt"] = date("Y-m-d H:i:s");
			$data["is_delete"] = 0;
			$data["pharmacy_password"] = md5($data["pharmacy_password"]);
			$data["admin_password"] = md5($data["admin_password"]);
			$sql = "SELECT MAX(pharmacy_id) as max_id FROM `t_pharmacy_info` WHERE pharmacy_id >= 10000;";
			$ret = $db->query($sql);
			$max_id = empty($ret[0]["max_id"])?10000:$ret[0]["max_id"];
			$this->log_trace("max_id:".$max_id);
			$data["pharmacy_id"] = ++$max_id;
			$this->log_trace(print_r($data,true));			
			$data = $db->data($data)->add();
			$this->ajaxReturn($data);
		}
		$table_brand = M("t_pharmacy_brand_info",null,$this->db_name);
		$data = $table_brand->select();
		$this->assign("brand_info",$data);
		$this->display();
		
	}
	
	
	
}