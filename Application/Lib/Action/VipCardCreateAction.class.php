<?php

class VipCardCreateAction extends CommonAction {

	public $table_name = "t_vip_password";
	public $db_name = DB_QUERY;
	public $update_key = "password";
	function __construct(){
		parent::__construct();
		$card_type = C("health_card_type");
		$this->assign("card_type",$card_type);
		$card_type = C("health_card_channel");
		$this->assign("card_channel",$card_type);
	}
	public function index(){
		$table_title = array(
				"card_type"	=>"健康卡类型",
				"card_channel"	=>"健康卡渠道",
				"password"	=>"卡号",
//				"vip_level"	=>"vip等级",
				"card_batch_num"	=>"健康卡生产批次",
				"card_time"	=>"卡时长(天)",
				"effect_duration"	=>"有效截止时间",
				"adv_quest_num"	=>"高级提问次数",
				"quest_closely_num"	=>"追问次数",
//				"is_delete"	=>"是否删除",
				"insert_dt"	=>"添加时间",
//				"action"	=>"操作提示",
		);
		$this->assign("table_title",$table_title);
		$this->display();
	}
	
	public function getList(){
		parent::getList();
		$parm_1 = $_POST["card_type"];
		$parm_2 = $_POST["card_batch_num"];
		$parm_3 = $_POST["card_channel"];
		$table = M($this->table_name,null,$this->db_name);		
		$where = " card_type like '%$parm_1%' AND card_batch_num like '%$parm_2%' AND card_channel LIKE '%$parm_3%'" ;
		$data = $table->where($where)->page($this->page,$this->rows)->order("insert_dt DESC")->select();
		$totalRecords = $table->where($where)->count();
		$card_type = C("health_card_type");
		$health_card_channel = C("health_card_channel");
		foreach ($data as $k=>$v){
			
			$data[$k]["card_time"] = $data[$k]["card_time"]/24/3600;
			$data[$k]["card_type"] = chn_flag($card_type, $data[$k]["card_type"]);
			$data[$k]["is_delete"] = chn_is($data[$k]["is_delete"]);
			$data[$k]["effect_duration"] = date("Y-m-d",$data[$k]["effect_duration"]);
			$data[$k]["card_channel"] = chn_flag($health_card_channel, $data[$k]["card_channel"]);
//					<a href="edit/?id='.$data[$k]["word_id"].'" class="easyui-linkbutton" iconCls="icon-edit" plain="true">编辑</a>
// 			$data[$k]["action"] = '
// 				<a href="javascript:void(0);" onclick="del('.$data[$k][$this->update_key].');" class="easyui-linkbutton" iconCls="icon-cancel" plain="true">删除</a>
// 							';
		}
		$table_data["total"] = $totalRecords;
		$table_data["rows"] = empty($data)?array():$data;
		$this->ajaxReturn($table_data);
	}
	
	//删除
	public function del(){
		$table = M($this->table_name,null,$this->table_name);
		$id = $_POST["id"];
		$where = array(
				$this->update_key 	=> $id,
				);
		$result = $table->where($where)->delete();
		flushMemcache();
		$this->ajaxReturn($result);
	}
	
	public function Add(){
		if(IS_POST ){
			$vip_level = 1;//暂时写1
			$card_num = I("card_num");
			$card_type = I("card_type");
			$card_channel = I("card_channel");
			$effect_duration = I("effect_duration");
			$adv_quest_num = I("adv_quest_num");
			$quest_closely_num = I("quest_closely_num");
			$card_time = I("card_time");
			$card_time = $card_time*24*3600;//单位 (秒)
			$effect_duration = strtotime($effect_duration);
			$db = M($this->table_name,null,$this->db_name);
			$sql = "SELECT MAX(card_batch_num) as batch_num from t_vip_password for update";
			$data = $db->query($sql);
			$card_batch_num = $data[0]["batch_num"] +1;
			$card_batch_num = $card_batch_num <2000?2000:$card_batch_num;
			$card_ret = array();
			set_time_limit(0);
			$result = 0;
			$cur_time = date("Y-m-d H:i:s");
			$db->execute("START TRANSACTION");
			$db->execute("set AUTOCOMMIT = 0 ");
			$inser_sql = "insert into t_vip_password(password,vip_level,card_channel,card_batch_num,card_type,card_time,effect_duration,adv_quest_num,quest_closely_num,is_delete,insert_dt)
			VALUES";
			while (count($card_ret) < $card_num){
				$random = rand(100000, 999999);
				$ret = $card_type.$card_channel.$random.$card_batch_num;
				$ret_arr = str_split($ret);
				$validate = array_sum($ret_arr);
				$validate = substr($validate, -2);
				$ret .= $validate;
				if(!in_array($ret, $card_ret)){
					array_push($card_ret, $ret);					
				}
			}
			$card_ret = array_chunk($card_ret, 1000);
			foreach ($card_ret as $each){
				$add_values = "";
				foreach ($each as $e){
					$add_values .= "('$e','$vip_level','$card_channel','$card_batch_num','$card_type','$card_time','$effect_duration','$adv_quest_num','$quest_closely_num','0','$cur_time'),";
				}
				$sql = $inser_sql.$add_values;
				$sql = substr($sql, 0,-1);
				$this->log_trace("sql:".$sql);
				$result = $db->execute($sql);
				if(!$result){
					$db->execute("ROLLBACK");
					break;
				}		
			}
			if($result){
				$db->execute("COMMIT");
			}
			$db->execute("set AUTOCOMMIT = 1 ");
			if($result){
				$log_db = M("t_log_vip_password",null,DB_LOG_USER);
				$log_data = array(
						"admin_id"     =>$this->getCurrentAdminId(),
						"admin_name"     =>$this->getLoginParam("admin_name"),
						"card_num"     =>$card_num,
						"vip_level"     =>$vip_level,
						"card_channel"     =>$card_channel,
						"card_batch_num"     =>$card_batch_num,
						"card_type"     =>$card_type,
						"effect_duration"     =>$effect_duration,
						"adv_quest_num"     =>$adv_quest_num,
						"quest_closely_num"     =>$quest_closely_num,
						"insert_dt"     =>$cur_time,
				);
				$log_db->add($log_data);
			}
			flushMemcache();
			$this->ajaxReturn($result);
		}
		$title ="添加";
		$this->assign("title",$title);
		$this->display();
		
	}
	
	
	
	
}