<?php

class BrandManageAction extends CommonAction {

	public $table_name = "t_pharmacy_brand_info";
	public $db_name = DB_QUERY;
	function __construct(){
		parent::__construct();
		
	}
	public function index(){
		$table_title = array(
				"brand_id"	=>"品牌ID",
				"brand_name"	=>"品牌名字",
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
		$where = "brand_name like '%{$this->brand_name}%'";
		$data = $table->where($where)->page($this->page,$this->rows)->order("insert_dt ASC")->select();
		$totalRecords = $table->where($where)->count();
		foreach ($data as $k=>$v){
			$data[$k]["is_delete"] = chn_is($data[$k]["is_delete"]);
			$data[$k]["action"] = '
					<a href="edit/?id='.$data[$k]["brand_id"].'" class="easyui-linkbutton" iconCls="icon-edit" plain="true">编辑</a>';
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
				$_POST["admin_password"] = md5($_POST["ad_password"]); 
			}
			unset($_POST["ad_password"]);
			$result = $db->data($_POST)->save();
			$sql = "UPDATE t_pharmacy_info set brand_name = '".$_POST["brand_name"]."' WHERE brand_id = '".$_POST["brand_id"]."'";
			if($result){
				$db->execute($sql);
			}
			$this->ajaxReturn($result);	
		}
		$title ="品牌编辑";
		$where = array(
			"brand_id" =>	$_GET["id"]
		);
		$data  = $db->where($where)->select();
		$data = $data[0];
		$edit_data = json_encode($data);
		$this->assign("data",$edit_data);
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
		$db = M($this->table_name,null,$this->db_name);
		if(IS_POST){
			$data = $_POST;
			$data["insert_dt"] = date("Y-m-d H:i:s");
			$sql = "SELECT MAX(brand_id) as max_id FROM `t_pharmacy_brand_info`;";
			$max = $db->query($sql);
			$max_id = ++$max[0]["max_id"];
			$data["brand_id"] = $max_id;
			$data["admin_password"] = md5($data["admin_password"]);
			
			$data = $db->data($data)->add();
			$this->ajaxReturn($data);
		}
		
		$this->display();
		
	}
	
	
	
}