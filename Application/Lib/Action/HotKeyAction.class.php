<?php

class HotKeyAction extends CommonAction {

	public $table_name = "t_sys_hot_key_word_cfg";
	
	function __construct(){
		parent::__construct();
		
	}
	public function index(){
		$table_title = array(
				"net_word"	=>"39健康网热词",
				"app_word"	=>"39管家热词",
				"word_weight"	=>"排序",
				"word_type"	=>"词类型",
				"insert_dt"	=>"添加时间",
				"action"	=>"操作提示",
		);
		$this->assign("table_title",$table_title);
		$this->display();
	}
	public function chn_word_type($type){
		if($type == 1){
			$chn = "找医生";
		}elseif ($type == 2){
			$chn = "问问题";
		}else{
			$chn = "查药";
		}
		return $chn;
	}
	public function getList(){
		parent::getList();
		$parm_1 = $_POST["net_word"];
		$parm_2 = $_POST["app_word"];
		$parm_3 = $_POST["word_type"];
		$table = M($this->table_name,null,DB_SYS);		
		$where = " net_word like '%$parm_1%' AND app_word like '%$parm_2%' AND word_type LIKE '%$parm_3%'" ;
		$data = $table->where($where)->page($this->page,$this->rows)->order("word_type asc ,word_weight ASC")->select();
		$totalRecords = $table->where($where)->count();
		foreach ($data as $k=>$v){
			$data[$k]["word_type"] = $this->chn_word_type($data[$k]["word_type"]);
			$data[$k]["action"] = '
					<a href="edit/?id='.$data[$k]["word_id"].'" class="easyui-linkbutton" iconCls="icon-edit" plain="true">编辑</a>
					<a href="javascript:void(0);" onclick="del('.$data[$k]["word_id"].');" class="easyui-linkbutton" iconCls="icon-cancel" plain="true">删除</a>
							';
		}
		$table_data["total"] = $totalRecords;
		$table_data["rows"] = empty($data)?array():$data;
		$this->ajaxReturn($table_data);
	}
	//编辑
	public function edit(){
		$db = M($this->table_name,null,DB_SYS);
		if(IS_POST){
			$json = $_POST["json"];
			$data = $this->jsonParse($json);
			$result = $db->data($data)->save();
			flushMemcache();
			$this->ajaxReturn($result);
		}
		$title ="热词编辑";
		$where = array(
			"word_id" =>	$_GET["id"]
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