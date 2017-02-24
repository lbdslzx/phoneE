<?php

class UserDataStepAction extends CommonAction {

	public $table_name = "t_user_step_count";
	public $db_name = DB_USER_1;
	
	function __construct(){
		parent::__construct();
		
	}
	public function index(){
		$table_title = array(
				"user_id"	=>"用户ID",
				"step_num"	=>"步数",
				"range_desc"	=>"范围",
				"color_value"	=>"颜色取值",
				"color_value_per"	=>"颜色取值百分比",
//				"short_desc"	=>"短建议",
				"device_id"	=>"设备",
				"insert_dt"	=>"添加时间",
				"is_delete"	=>"是否已删除",
		);
		$device_id = array(
				"0"	=> "手动输入",
				"1"	=> "血压仪",
				"2"	=> "智能秤",
				"3"	=> "血糖仪",
				"4"	=> "健康手环",
				"5"	=> "康康血压仪",
				"6"	=> "PICOO智能秤 ",
				"7"	=> "GSM血糖仪(爱奥乐)",
				"100"=>	"苹果内置计步器"
		);
		$table = M($this->table_name,null,$this->db_name);
		$begin_time =  $table->field("min(insert_dt)")->select();
		$this->assign("table_title",$table_title);
		$this->assign("device",$device_id);
		$this->assign("begin_time",empty($begin_time) ? time() : strtotime($begin_time[0]['min(insert_dt)']));
		$this->display();
	}
	
	public function getList(){
		parent::getList();
		$table = M($this->table_name,null,$this->db_name);		
//		$where = " net_word like '%$parm_1%' AND app_word like '%$parm_2%' AND word_type LIKE '%$parm_3%'" ;
		$where = "user_id like '%{$this->user_id}%' and user_id != 120";
		if(!empty($this->begin_dt) && !empty($this->end_dt)){
			$where = $where." and insert_dt > '{$this->begin_dt}' and insert_dt < '{$this->end_dt}'";
		}
		if($this->equipment_id != -1 && !empty($this->equipment_id)){
			$where = $where." and device_id = '{$this->equipment_id}'";
		}
		$data = $table->where($where)->page($this->page,$this->rows)->order("step_num DESC, insert_dt DESC")->select();
		$totalRecords = $table->where($where)->count();
		foreach ($data as $k=>$v){
			$data[$k]["is_delete"] = chn_is($data[$k]["is_delete"]);
			$data[$k]["device_id"] = chn_device_id($data[$k]["device_id"]);
			$data[$k]["user_id"] = '<a href="javascript:void(0);" onclick="view_user_info('.$data[$k]["user_id"].');" class="easyui-linkbutton" iconCls="icon-cancel" style="text-decoration: none;" title="点击查看用户资料" plain="true">'.$data[$k]["user_id"].'</a>';
// 			$data[$k]["action"] = '
// 					<a href="edit/?id='.$data[$k]["word_id"].'" class="easyui-linkbutton" iconCls="icon-edit" plain="true">编辑</a>
// 					<a href="javascript:void(0);" onclick="del('.$data[$k]["word_id"].');" class="easyui-linkbutton" iconCls="icon-cancel" plain="true">删除</a>
// 							';
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