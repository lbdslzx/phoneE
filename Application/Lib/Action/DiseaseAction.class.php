<?php

class DiseaseAction extends CommonAction {

	function __construct(){
		parent::__construct();
	}
	public function index(){
		$table_title = array(
				"index_letter"	=>"索引字母",
//				"father_id"	=>"上级分类名称",
				"net_disease_name"	=>"39网疾病名称",
				"app_disease_name"	=>"39健康管家疾病名称",
// 				"is_delete"	=>"删除状态",
				"insert_dt"	=>"添加时间",
				"action"	=>"操作提示",
		);
		$this->assign("table_title",$table_title);
		$db = M("t_sys_disease_cfg",null,DB_SYS);
		$f_cat = $db->where("father_id = 0")->select();
		$this->assign("f_cat",$f_cat);
		$this->display();
	}
	public function getList(){
		parent::getList();
		$net_disease_name = $_POST["net_disease_name"];
		$app_disease_name = $_POST["app_disease_name"];
		$index_letter = $_POST["index_letter"];
		$father_id = I("father_id");
		if($this->father_id != "" && $this->father_id != -1){
			$father_cond = " AND father_id = '$father_id'";
		}
		$table = M("t_sys_disease_cfg",null,DB_SYS);		
		$where = "index_letter like '%$index_letter%'
		 AND net_disease_name like '%$net_disease_name%'
		  AND app_disease_name like '%$app_disease_name%'
			$father_cond
		" ;
		$data = $table->where($where)->page($this->page,$this->rows)->order("insert_dt desc")->select();
		$totalRecords = $table->where($where)->count();
		foreach ($data as $k=>$v){
			$data[$k]["is_delete"] = chn_is_del($data[$k]["is_delete"]);
			$data[$k]["action"] = '
					<a href="edit/?id='.$data[$k]["id"].'" class="easyui-linkbutton" iconCls="icon-edit" plain="true">编辑</a>
					<a href="javascript:void(0);" onclick="del('.$data[$k]["id"].');" class="easyui-linkbutton" iconCls="icon-cancel" plain="true">删除</a>
							';
		}
		$table_data["total"] = $totalRecords;
		$table_data["rows"] = empty($data)?array():$data;
		$this->ajaxReturn($table_data);
	}
	//编辑
	public function edit(){
		$db = M("t_sys_disease_cfg",null,DB_SYS);
		if(IS_POST){
			$json = $_POST["json"];
			$data = $this->jsonParse($json);
				
			$result = $db->save($data);
			flushMemcache();
			$this->ajaxReturn($result);
		}
		$title ="编辑";
		$where = array(
			"id" =>	$_GET["id"]
		);
		$data  = $db->where($where)->select();
		$data = $data[0];
		$edit_data = json_encode($data);
		$this->assign("data",$edit_data);
		$this->assign("title",$title);
		$f_cat = $db->where("father_id = 0")->select();
		$this->assign("f_cat",$f_cat);
		$this->display();

		
	}
	//删除
	public function del(){
		$table = M("t_sys_disease_cfg",null,DB_SYS);
		$id = $_POST["id"];
		$where = array(
				"id" 	=> $id,
				);
		$result = $table->where($where)->delete();
		flushMemcache();
		$this->ajaxReturn($result);
		flushMemcache();
	}
	
	public function Add(){
		$db = M("t_sys_disease_cfg",null,DB_SYS);
		if(IS_POST){
			$json = $_POST["json"];
			$data = $this->jsonParse($json);
			$data["is_delete"] = 0;
			$data["insert_dt"] = date("Y-m-d H:i:s");			
			$result = $db->add($data);
			flushMemcache();
			$this->ajaxReturn($result);
		}
		$title ="添加";
		$this->assign("title",$title);
		$f_cat = $db->where("father_id = 0")->select();		
		$this->assign("f_cat",$f_cat);
		$this->display();

	}
	
	
	
}