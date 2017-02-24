<?php
/**
 * @example      财务报表
 * @file         FinancialStatementAction.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2016-03-23
 * @time         18:32
 * @copyright © 2016, Longmaster Corporation. All right reserved.
 */
class FinancialStatementAction extends CommonAction{
    #日报表 BEGIN#
    public function index(){
        $timeTo = date('Y-m-d',strtotime("-1 day"));
        $timeFor = date("Y-m-d",strtotime("-8 day"));
        $this->assign('timeFor',$timeFor);
        $this->assign('timeTo',$timeTo);
        $remitUrl = C("currency_cfg.remit_url");
        $this->assign("remitUrl",$remitUrl);
        $this->display('financial_statement_list');
    }
    public function getFinancialStatementList(){
        parent::getList();
        $timeFor = isset($_POST['time_for']) ? $_POST['time_for'] :date('Y-m-d',strtotime("-8 day"));
        $timeTo = isset($_POST['time_to']) ? $_POST['time_to'] : date("Y-m-d",strtotime("-1 day"));
        $where = "stat_dt BETWEEN '{$timeFor}' AND '{$timeTo}'";
        $db = M('t_stat_daily_registration_report',null,DB_STAT);
        $field = [
            "DATE_FORMAT(stat_dt, '%Y-%m-%d') AS '应汇款日'",
            "SUM(CASE WHEN type_id = 1 THEN type_value ELSE 0 END) AS '昨日盈余'",
            "SUM(CASE WHEN type_id = 2 THEN type_value ELSE 0 END) AS '昨日已支付'",
            "SUM(CASE WHEN type_id = 3 THEN type_value ELSE 0 END) AS '昨日已审批退款'",
            "SUM(CASE WHEN type_id = 4 THEN type_value ELSE 0 END) AS '汇款结余'",
            "SUM(CASE WHEN type_id = 5 THEN type_value ELSE 0 END) AS '应汇款'",
            "state",
        ];
        $list = $db->where($where)
            ->field($field)
            ->group("DATE_FORMAT(stat_dt, '%Y-%m-%d')")
            ->order("stat_dt DESC")
            //->page($this->page,$this->rows)
            ->select();
        $table_data["rows"] = (array)$list;
        $this->ajaxReturn($table_data);
    }
    public function remittance(){
        parent::getList();
        $stat_dt = $this->stat_dt;
        $db = M('t_stat_daily_registration_report',null,DB_STAT);
        $data = [
            'stat_dt' => $stat_dt,
            'state'   => 1
        ];
        $result = $db->where("stat_dt = '{$stat_dt}'")->save($data);
        $this->ajaxReturn($result);
    }
    public function remittances(){
        parent::getList();
        $where = ["stat_dt IN({$this->stat_dt})"];
        $data = ["state"=>1];
        $db = M('t_stat_daily_registration_report',null,DB_STAT);
        $result = $db->where($where)->save($data);
        $this->ajaxReturn($result);
    }
    #日报表 END#
    #周报表 BEGIN#
    public function weeklyRegistrationReport(){
        $param = $this->getLoginParam();
        $level = explode(" ",trim($param['admin_level']));
        $permissions = (in_array("A",$level) || in_array("weeklyRegistrationReport",$level) || in_array("weeklyRegistrationReport-role-remit",$level)) ? "remit" : "see";
        $surplus = (in_array("A",$level) || in_array("weeklyRegistrationReport",$level) || in_array("weeklyRegistrationReport-role-surplus",$level)) ? "true" : "false";
        $timeTo = date('Y-m-d');
        $timeFor = date("Y-m-d",strtotime("-1 month"));
        $this->assign('timeFor',$timeFor);
        $this->assign('timeTo',$timeTo);
        $remitUrl = C("currency_cfg.remit_url");
        $this->assign("remitUrl",$remitUrl);
        $this->assign("permissions",$permissions);
        $this->assign("surplus",$surplus);
        $this->display('weekly_registration_report');
    }
    ///周汇款报表列表
    public function getWeeklyRegistrationReportList(){
        $list = $this->_getWeeklyRegistrationReportList();
        //$total = $db->where($where)->count();
        $result = [
            //"total"     => $total,
            "rows"      => $list,
        ];
        $this->ajaxReturn($result);
    }
    private function _getWeeklyRegistrationReportList(){
        parent::getList();
        $timeFor = $this->time_for ? $this->time_for : date("Y-m-d",strtotime("-1 month"));
        $timeTo = $this->time_to ? $this->time_to : date("Y-m-d");
        $where = [
            "start_dt <= '{$timeFor}' AND end_dt >= '{$timeFor}'",
            "start_dt >= '{$timeFor}' AND end_dt <= '{$timeTo}'",
            "start_dt <= '{$timeTo}' AND end_dt >= '{$timeTo}'",
        ];
        $now = date("Y-m-d");
        $where = "((".implode(") OR (",$where).")) AND end_dt < '{$now}'";
        $db = M("t_stat_weekly_registration_report",null,DB_STAT);
        $field = [
            "start_dt",
            "CONCAT_WS('-',DATE_FORMAT(start_dt,'%Y%m%d'),DATE_FORMAT(end_dt,'%Y%m%d')) AS stat_dt",
            "surplus_value AS surplus_value",
            "ghao_value AS ghao_value",
            "ghao_back_value AS ghao_back_value",
            "remit_value AS remit_value",
            "op_name as op_name",
            "remark AS remark",
            "IF(remit_state=1,CONCAT_WS('',substring_index(remark,'#',1),'已汇款'),'未汇款') AS remit_state",
            "insert_dt"
        ];
        $list = $db->field($field)
            ->where($where)
            ->page($this->page,$this->rows)
            ->order("remit_state ASC,start_dt DESC")
            ->select();
        return (array)$list;
    }
    ///确认汇款
    public function weeklyRegistrationReportRemit(){
        parent::getList();
        $startDat = $this->start_dt;
        $param = $this->getLoginParam();
        $opeName = $param['user_name'];
        $where = "start_dt IN('".implode("','",$startDat)."')";
        ///日期#时间#周期#金额
        $remark = date("Ymd")."#".date("H:i:s")."#".implode(",",$this->interval)."#￥".number_format($this->total,2);
        $data = [
            "remit_state"       => 1,
            "remark"            => $remark,
            "opeName"           => $opeName
        ];
        $db = M("t_stat_weekly_registration_report",null,DB_STAT);
        $result = $db->where($where)->save($data);
        $this->ajaxReturn($result);
    }
    public function exportExcel(){
        $list = $this->_getWeeklyRegistrationReportList();
        $param = $this->getLoginParam();
        $level = explode(" ",trim($param['admin_level']));
        $surplus = (in_array("A",$level) || in_array("weeklyRegistrationReport",$level) || in_array("weeklyRegistrationReport-role-surplus",$level)) ? "true" : "false";
        $head = [
            ["key"=>"stat_dt","value"=> "汇款周期"],
            ["key"=>"surplus_value","value"=> "手续费盈余(元)"],
            ["key"=>"ghao_value","value"=> "挂号费（元）"],
            ["key"=>"ghao_back_value","value"=> "挂号费退款（元）"],
            ["key"=>"remit_value","value"=> "应汇款（元）"],
            ["key"=>"remit_state","value"=> "汇款状态"]
        ];
        if($surplus=="false"){
            unset($head['surplus_value']);
        }
        $this->exportExcelCsv("汇款报表","汇款报表",$head,$list);
    }
    #周报表 end#
}