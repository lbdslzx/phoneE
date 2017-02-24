<?php

/**
 * Created by PhpStorm.
 * User: daxin.yang@longmaster.com.cn<daxin.yang@longmaster.com.cn>
 * Date: 2016/2/17
 * Time: 19:42
 * 功能说明：包月会员数量查询
 */
class VipCountQueryAction extends CommonAction
{

    public $table_name = 't_stat_daily_vip_report';
    public $db;

    function __construct()
    {
        parent::__construct();
        $this->db = M($this->table_name, null, DB_STAT);
    }

    public function index()
    {
        $table_title = array(
            "统计时间" => "统计时间",
            "包月人数" => "包月人数",
            "包月金额" => "包月金额"
        );
        $this->assign("table_title", $table_title);
        $this->display();
    }

    function getList()
    {
        parent::getList();

        //统计开始时间
        $beginDt = date('Y-m-d',strtotime("-1 month"));
        //统计结束时间
        $endDt = date('Y-m-d');

        if(!empty($_POST['beginDt']) && !empty($_POST['endDt'])){
            $beginDt = $this->beginDt;
            $endDt = $this->endDt;
        }

        $res = $this->getTypeName($beginDt, $endDt);

        $fields = array(
            "DATE_FORMAT(stat_dt, '%Y-%m-%d') AS '统计时间'"
        );
        foreach ($res as $key => $value) {
            $str = "SUM(CASE WHEN type_name='" . $value["type_name"] . "' THEN type_value ELSE 0 END) AS '" . $value["type_name"] . "'";
            array_push($fields, $str);
        }

        $where = "stat_dt>= '{$beginDt}' AND stat_dt<='{$endDt}'";

        //列表页内容
        $data = $this->db->field($fields)
            ->where($where)
            ->group("DATE_FORMAT(stat_dt, '%Y-%m-%d')")
            ->page($this->page, $this->rows)
            ->select();

        if(empty($data)){
            $data = array();
        }

        $table_data["total"] = count($data);
        $table_data["rows"] = $data;
        $this->ajaxReturn($table_data);
    }

    function getTypeName($bt, $et)
    {
        $sql = "SELECT DISTINCT type_name from t_stat_daily_vip_report WHERE stat_dt>='{$bt}' AND stat_dt<='{$et}';";
        $data = $this->db->query($sql);
        return $data;
    }

}