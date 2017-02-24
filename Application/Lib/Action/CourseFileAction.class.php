<?php

class CourseFileAction extends CommonAction {
	/*
	 * 列出管理员列表 
	 */
	
	function __construct(){
		parent::__construct();
	}
	public function index(){
		$table_title = array(
				"doctor_name"	=>"医生名称",
				"doctor_department"	=>"所在科室",
				"file_title"	=>"文件标题",
				"file_desc"	=>"音频文件描述",
				"file_name"	=>"音频文件名称",
				"file_duration"	=>"音频时长",
				"picture_name"	=>"图片名称",
				"is_recommend"	=>"是否推荐",
				"recommend_begin_dt"	=>"推荐起始时间",
				"recommend_end_dt"	=>"推荐结束时间",
// 				"is_delete"	=>"是否删除",
				"upd_dt"	=>"排序时间",
				"action"	=>"操作提示",
		);
		$this->assign("table_title",$table_title);
		$this->display();
	}
	public function getList(){
		parent::getList();
		$file_title = $_POST["file_title"];
		$start_time = $_POST["start_time"];
		$end_time = $_POST["end_time"];
		$table = M("t_sys_ivr_file_cfg",null,DB_SYS);
		if(!empty($start_time)){
			$where = "file_title like '%$file_title%' AND recommend_begin_dt >= '$start_time' AND recommend_begin_dt <='$end_time'" ;
		}
		$data = $table->where($where)->page($this->page,$this->rows)->order("upd_dt desc")->select();
// 		UserLog::logWrite($table->getLastSql());
		$totalRecords = $table->where($where)->count();
		foreach ($data as $k=>$v){
			$data[$k]["is_delete"] = chn_is_del($data[$k]["is_delete"]);
			$data[$k]["is_recommend"] = chn_is($data[$k]["is_recommend"]);
//			$data[$k]["file_name"] = $data[$k]["file_name"].'<a href="javascript:void(0);" onclick="play(\''.$data[$k]["file_name"].'\');">播放</div>';
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
		$db = M("t_sys_ivr_file_cfg",null,"db_sys");
		if(IS_POST){
			$json = $_POST["json"];
			$data = $this->jsonParse($json);
			$result = $db->data($data)->save();
			$this->ajaxReturn($result);
		}
		$title ="IVR文件编辑";
		$hfs_addr = C("hfs_addr");
		$this->assign("hfs_addr",$hfs_addr);
		$where = array(
			"id" =>	$_GET["id"]
		);
		$data  = $db->where($where)->select();
		$data = $data[0];
		$edit_data = json_encode($data);
		$this->assign("data",$edit_data);
		$this->assign("pic_url",$hfs_addr."3006/5/".$data["picture_name"]);
		$this->assign("title",$title);
		$this->display();
		flushMemcache();
		
	}
	//删除
	public function del(){
		$table = M("t_sys_ivr_file_cfg",null,DB_SYS);
		$id = $_POST["id"];
		$where = array(
				"id" 	=> $id,
				);
		$result = $table->where($where)->delete();
		flushMemcache();
		$this->ajaxReturn($result);
	}
	
	public function Add(){
		if(IS_POST){
			$json = $_POST["json"];
			$data = $this->jsonParse($json);
			$data["is_delete"] = 0;	
			$db = M("t_sys_ivr_file_cfg",null,DB_SYS);
			$result = $db->add($data);
			flushMemcache();
			$this->ajaxReturn($result);
		}
		$title ="IVR文件添加";
		import("@.CustomLib.HfsModel");
		$hfsmodel = new HfsModel(HfsModel::OP_TYPE_IVR_FILE, "temp_file.name", $this->getCurrentAdminId());
		$json = $hfsmodel->getJson();
		$hfs_addr = C("hfs_addr");
		$json = urlencode($json);
		$upload_addr = $hfs_addr."?json=".$json;
		$this->assign("hfs_addr",$hfs_addr);
		$this->assign("upload_addr",$upload_addr);
		$this->assign("title",$title);
		$this->assign("data_item",$data_item);
		$this->display();
		
	}
	
	public function play(){
		$hfs = new HfsModel(HfsModel::OP_TYPE_IVR_FILE_DOWN);
		$down_url = $hfs->getDownUrl();
		$addr = $down_url.$this->_param(2);		
		$this->assign("addr",$addr);
		$this->display();
	}
	
	
}