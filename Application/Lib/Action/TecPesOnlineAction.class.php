<?php

class TecPesOnlineAction extends CommonAction {

	public $table_name = "t_log_user_online_count";
	public $db_name = DB_LOG_USER;
	
	function __construct(){
		parent::__construct();
		
	}
	public function index(){
		$table_title = array(
//				"pes_id"	=>"PES ID",
				"ios_online_num"	=>"ios在线数",
				"android_online_num"	=>"Android在线数",
				"online_user_num"	=>"在线总数",				
				"insert_dt"	=>"日期",
		);
		$this->assign("table_title",$table_title);
		$pes_db = M("t_pes_info",null,DB_SYS);
		$pes_info = $pes_db->order("pes_id asc")->select();
		$this->assign("pes_info",$pes_info);
		// 获取表数据
		parent::getList();
		$table = M($this->table_name,null,$this->db_name);
		$date = I("date");
		$date = empty($date)?date("Y-m-d"):$date;
		$this->assign("date",$date);
		$pes_id = I("pes_id");
		$pes_id = empty($pes_id)?65535:$pes_id;
		$where = "insert_dt like'$date%' and pes_id like '%$pes_id%' ";
		$data = $table->where($where)->order("insert_dt ASC")->select();
		//		$totalRecords = $table->where($where)->count();
		$table_data["total"] = 15;
		$table_data["rows"] = empty($data)?array():$data;
		$this->assign("table_data",json_encode($table_data));
		//计算总数
		$sql = "SELECT pes_id, online_user_num,
				DATE_FORMAT(insert_dt,'%X-%m-%d %H:%i') as insert_dt
				FROM `t_log_user_online_count` WHERE
				insert_dt like '$date%'  ORDER BY insert_dt ASC";
		$pes_total = $table->query($sql);
		
		foreach ($pes_total as $each){
			$each["insert_dt"] = date("H:i",strtotime($each["insert_dt"]));
			$pes[$each["pes_id"]][] = $each; 
			$x_data[] = $each["insert_dt"];
// 			$y_data[] = $each["online_user_num"];
		}
		$y_data = array();
		foreach ($pes as $key=>$each){
			$y_data[$key]["name"] = $key;
			foreach ($each as $e){
				$y_data[$key]["data"][] = $e["online_user_num"];
			}
		}
		if(isset($y_data["65535"])){
			$y_data["65535"]["name"] = "'总计'";
// 			print_r($y_data["65535"]["name"]);exit;
		}
// 		print_r($y_data);exit;
		$y_data = array_values($y_data);
		$x_data = array_unique($x_data);
		$x_data = array_values($x_data);
		$x_data = json_encode($x_data);
		$x_data = str_replace("\"", "'", $x_data);
		$y_data = json_encode($y_data);
		$y_data = str_replace("\"", "", $y_data);
// 		print_r($y_data);exit;
		$json_data = "{
        title: {
            text: 'PES用户在线数统计',
            x: -20 //center
        },
        subtitle: {
            text: '折线统计图',
            x: -20
        },
        xAxis: {
            categories: $x_data
        },
        yAxis: {
            title: {
                text: '用户在线数 (人)'
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
            valueSuffix: '人'
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
        series:  $y_data
       
    }";

		$this->assign("json",$json_data);
		$this->display();
		
		
	}
	public function getList(){
		parent::getList();
		$table = M($this->table_name,null,$this->db_name);	
		$date = I("date");
		$date = empty($date)?"2015-05-22":$date;
		$pes_id = I("pes_id");
		$where = "insert_dt like'$date%' and pes_id like '%$pes_id%' and pes_id != 65535";
		$data = $table->where($where)->order("insert_dt DESC")->select();
//		$totalRecords = $table->where($where)->count();
		$table_data["total"] = 15;
		$table_data["rows"] = empty($data)?array():$data;
		$this->ajaxReturn($table_data);
	}
	
	public function updateChart(){
		$json  = "
				
				";
		echo $json;
	}
	
	
	
	
	
}