<?php
/**
 * @example      客户端活跃数统计
 * @file         StatActiveDeviceAction.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2016-06-13
 * @time         16:19
 * @copyright © 2016, Longmaster Corporation. All right reserved.
 */
class StatActiveDeviceAction extends CommonAction{
    private $_timeFor,$_timeTo,$_province,$_tableName='t_stat_daily_active_report',$_dbName=DB_STAT;
    public function __construct(){
        parent::__construct();
        $province = file_get_contents(RESOURCE_PUBLIC."json/Province.json");
        $this->_province = json_decode($province,true);
        $this->_timeFor = date("Y-m-d",strtotime("-32 day"));
        $this->_timeTo = date("Y-m-d",strtotime("-1 day"));
    }
    public function index(){
        $this->assign("timeFor",$this->_timeFor);
        $this->assign("timeTo",$this->_timeTo);
        $this->display();
    }
    public function getList(){
        parent::getList();
        $table = M($this->_tableName,null,$this->_dbName);
        $timeFor = empty($this->time_for) ? $this->_timeFor : $this->time_for;
        $timeTo = empty($this->time_to) ? $this->_timeTo : $this->time_to;
        $where = ["stat_dt BETWEEN '{$timeFor}' AND '{$timeTo}'"];
        $where[] = ['module_id = 1'];
        $field = [
            "DATE_FORMAT(stat_dt, '%Y-%m-%d') AS stat_dt",
            "SUM(CASE WHEN type_id = 1 THEN type_value ELSE 0 END) AS IOS",
            "SUM(CASE WHEN type_id = 2 THEN type_value ELSE 0 END) AS Android",
        ];
        $list = $table->where($where)
            ->field($field)
            ->group("DATE_FORMAT(stat_dt, '%Y-%m-%d')")
            ->order("stat_dt DESC")
            ->select();
        $this->ajaxReturn(['total'=>count($list),'rows'=>(array)$list]);
    }
    public function indexNew(){
        $this->assign("province",$this->_province);
        $this->assign("provinceJson",json_encode($this->_province));
        $this->assign("timeFor",$this->_timeFor);
        $this->assign("timeTo",$this->_timeTo);
        $this->display("stat_active_dev_list");
    }
    public function getNewList(){
        parent::getList();
        $table = M("t_stat_daily_user_distribute_report",null,$this->_dbName);
        $timeFor = empty($this->time_for) ? $this->_timeFor : $this->time_for;
        $timeTo = empty($this->time_to) ? $this->_timeTo : $this->time_to;
        $where = ["stat_dt BETWEEN '{$timeFor}' AND '{$timeTo}'"];
        $where[] = ['type_id = 2'];
        $field = [
            "DATE_FORMAT(stat_dt, '%Y-%m-%d') AS stat_dt",
        ];
        foreach ($this->_province as $key => $value){
            $field[] = "SUM(IF(area_id='{$value['id']}',CASE WHEN phone_os = 0 THEN type_value ELSE 0 END,0)) AS IOS_{$value['id']}";
            $field[] = "SUM(IF(area_id='{$value['id']}',CASE WHEN phone_os = 1 THEN type_value ELSE 0 END,0)) AS Android_{$value['id']}";
        }
        $list = $table->where($where)
            ->field($field)
            ->group("DATE_FORMAT(stat_dt, '%Y-%m-%d')")
            ->order("stat_dt DESC")
            ->page($this->page,$this->rows)
            ->select();
        $result = $table->where($where)
            ->field("COUNT(DISTINCT stat_dt) AS total_num")
            ->find();
        $list = is_array($list) ? $list : [] ;
        $province = $this->province;
        $province = ($province === false OR $province === null) ? -1 : $province;
        array_push($list,['province'=>$province]);
        $this->ajaxReturn(['total'=>$result['total_num']-1,'rows'=>$list]);
    }
}