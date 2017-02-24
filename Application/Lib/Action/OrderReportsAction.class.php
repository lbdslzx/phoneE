<?php
/**
 * @example      挂号订单
 * @file         OrderReportsAction.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2016-03-23
 * @time         18:30
 * @copyright © 2016, Longmaster Corporation. All right reserved.
 */
class OrderReportsAction extends CommonAction{
    private $_adminId,$_adminLevel,$_timeFor,$_timeTo;
    public function __construct(){
        parent::__construct();
        $this->_adminId = $this->getCurrentAdminId();
        $this->_adminLevel = $this->_permissions();
        import("@.CustomLib.Push");
        import("@.CustomLib.payment");
        import("@.CustomLib.ArrayToXML");
        import("@.CustomLib.Common");
        import("@.CustomLib.CryptAES");
        $this->_timeFor = date('Y-m-d H:i:s',strtotime('-32 day'));
        $this->_timeTo = date('Y-m-d H:i:s',strtotime('-1 day'));
    }
    private function _permissions(){
        $param = $this->getLoginParam();
        $level = explode(" ",trim($param['admin_level']));
        $permissions = [
            'refund_business_audit'     => (in_array('A',$level)
                || in_array('order_refund_reports-role-business_audit',$level)
                || in_array("order_refund_reports",$level)
                || in_array('financial_manage',$level)) ? true : false,
            'refund_financial_audit'    => (in_array('A',$level)
                || in_array('order_refund_reports-role-financial_audit',$level)
                || in_array("order_refund_reports",$level)
                || in_array('financial_manage',$level)) ? true : false,
            'apply_for_refund'          => (in_array('A',$level)
                || in_array('order_reports-role-apply_for_refund',$level)
                || in_array('order_reports',$level)
                || in_array('financial_manage',$level)) ? true : false,
            'user_info_encryption'      => (in_array('user_info_no_encryption',$level)
                || in_array('financial_manage',$level)
                || in_array('A',$level)) ? false : true
        ];
        return $permissions;
    }
    public function index(){
        $db = M('t_registration_order_info',null,DB_REGISTRATION_NET);
        ///现有挂号医院
        $hospital = $db->distinct('hosl_name')
            ->field('hosl_name')
            ->select();
        ///现有支付方式
        $sql = "SELECT
        DISTINCT IF(pay_type IN(0,5),'0,5',pay_type) AS pay_type,
        CASE pay_type WHEN 0 THEN '&nbsp;'
        WHEN 1 THEN '支付宝'
        WHEN 2 THEN '微信-APP'
        WHEN 3 THEN '银联'
        WHEN 4 THEN '微信-公众号'
        WHEN 5 THEN '&nbsp;'
        ELSE '未知' END AS pay_type_name
        FROM t_registration_order_info";
        $payType = $db->query($sql);

        ///权限
        $permissions = json_encode($this->_adminLevel);
        ///返回前端视图
        $this->assign('timeFor',$this->_timeFor);
        $this->assign('timeTo',$this->_timeTo);
        $this->assign('hospital',$hospital);
        $this->assign('payType',$payType);
        $this->assign("permissions",$permissions);
        $this->display('order_report_list');
    }
    /**
     * 获取订单列表
     */
    public function getOrderReportList(){
        parent::getList();
        $page = intval($this->page) <= 0 ? 1 : $this->page;
        $for = ($page - 1)*$this->rows;
        $page = $this->rows;
        $where = $this->_orderRequestWhereDeal($_REQUEST);
        $result = $this->_getOrderListByWhere($where,$for,$page);
        $list = $result['rows'];
        $totalRecords = $result['total'];
        $table_data["total"]    = $totalRecords+1;// > $page ? $totalRecords : count($list+1);
        array_push($list,$result['sum']);
        $table_data["rows"]     = $list;
        $table_data['sum']      = $result['sum'];
        $this->ajaxReturn($table_data);
    }
    ///条件处理
    private function _orderRequestWhereDeal($input){
        $advanced = $_REQUEST['advanced'];
        $where = ["1 = 1"];
        ///时间段
        if(isset($input['time_frame'])){
            switch($input['time_frame']){
                case 1:///下单时间
                    $where[] = "b.insert_dt BETWEEN '{$input['time_for']} 00:00:00' AND '{$input['time_to']} 23:59:59'";
                    break;
                case 2:///预约时间
                    $where[] = "b.shift_date BETWEEN '{$input['time_for']} 00:00:00' AND '{$input['time_to']} 23:59:59'";
                    break;
                case 3:///支付时间
                    $where[]= "b.pay_dt BETWEEN '{$input['time_for']} 00:00:00' AND '{$input['time_to']} 23:59:59'";
                    break;
                case 4:///申请退款时间
                    $where[]= "a.create_dt BETWEEN '{$input['time_for']} 00:00:00' AND '{$input['time_to']} 23:59:59'";
                    break;
                case 5:///审批退款时间
                    $where[]= "(a.busi_approve_dt BETWEEN '{$input['time_for']} 00:00:00' AND '{$input['time_to']} 23:59:59' OR a.fina_approve_dt BETWEEN '{$input['time_for']} 00:00:00' AND '{$input['time_to']} 23:59:59')";
                    break;
                default:
                    $where[]= "b.pay_dt BETWEEN '{$this->_timeFor} 00:00:00' AND '{$this->_timeTo} 23:59:59'";
                    break;
            }
        }else{
            $where[] = "b.pay_dt BETWEEN '{$this->_timeFor} 00:00:00' AND '{$this->_timeTo} 23:59:59'";
        }
        ///支付渠道
        if(isset($input['pay_type']) && $input['pay_type'] != ''){
            $where[] = "b.pay_type IN({$input['pay_type']})";
        }
        ///支付状态
        if(isset($input['pay_state']) && $input['pay_state'] != ''){
            if($input['pay_state']=='1,9'){///线下支付
                $where[] = "(b.pay_state IN({$input['pay_state']}) OR b.pay_type IN(5))";
                //$where .= " AND b.pay_type IN(5)";
            }else {
                $input['pay_state'] = $input['pay_state'] == 14 ? 5 : $input['pay_state'];
                if ($input['pay_state'] > 9) {
                    $auditState = $input['pay_state'] == 11 ? '2,5' : $input['pay_state'] - 9;
                    $where[] = "a.apply_status IN($auditState)";
                } else {
                    $where[] = "b.pay_state IN({$input['pay_state']})";
                    if ($input['pay_state'] == 5) {
                        $where[] = "a.apply_status IN(6)";
                    }
                }
            }
        }elseif(!isset($input['pay_state'])){
            $where[]= "b.pay_state = 0";
        }

        if($advanced == 1){
            //挂号医院
            if(isset($input['hospital']) && $input['hospital'] != ''){
                $where[] = "b.hosl_name LIKE '%{$input['hospital']}%'";
            }
            ///支付方式
            if(isset($input['payment'])){
                switch($input['payment']){
                    case 0:break;
                    case 1:
                        $where[] = "b.pay_state NOT IN(1,9) AND b.pay_type <> 5";
                        break;
                    case 2:
                        $where[]= "(b.pay_state IN(1,9) OR b.pay_type = 5)";
                        break;
                    default:
                        break;
                }
            }else{//默认线上
                $where[]= "(b.pay_state NOT IN(1,9) OR b.pay_type <> 5)";
            }
            ///根据订单ID查询
            if(isset($input['order_id']) && $input['order_id'] != ''){
                $where = ["b.order_id = '{$input['order_id']}'"];
            }
        }
        return $where;
    }
    /**
     * 订单信息
     */
    public function orderInfo(){
        parent::getList();
        $orderId = $this->order_id;
        $db = M('t_registration_order_info',null,DB_REGISTRATION_NET);
        $orderInfo = $db->where("order_id = '{$orderId}'")
            ->field("medical_book_price,medical_card_price,order_id,insert_dt,hosl_name,dept_name,service_fee,expert_name,reg_fee,CONCAT_WS(' ',shift_date,time_section) AS shift_date,patient_name,certificate_type,certificate_no,patient_phone,pay_type,pay_dt,pay_state,pay_no")
            ->find();
        $order = [
            'order_id'      => $orderId,
            'insert_dt'     => $orderInfo['insert_dt'],
            'hospital'      => $orderInfo['hosl_name'],
            'department'    => $orderInfo['dept_name'],
            'doctor'        => $orderInfo['expert_name'],
            'reg_fee'       => number_format($orderInfo['reg_fee'],2)."元",
            'patient_name'  => $orderInfo['patient_name'],
            'patient_phone' => $orderInfo['patient_phone'],
            'id_card'       => $orderInfo['certificate_no'],
            'id_card_type'  => $orderInfo['certificate_type'],
            'shift_date'    => $orderInfo['shift_date'],
            'medical_card_price' => number_format($orderInfo['medical_card_price'],2)."元",
            'medical_book_price' => number_format($orderInfo['medical_book_price'],2)."元"
        ];
        if($this->_adminLevel['user_info_encryption']){
            $order['id_card'] = $this->hideString($order['id_card'],6,13);
            $order['patient_phone'] = $this->hideString($order['patient_phone'],3,6);
            $order['patient_name'] = $this->hideString($order['patient_name']);
        }
        $this->assign('order',$order);
        // 检查费
        $orderId = $this->order_id;
        $db = M('t_reg_check_info', null, DB_REGISTRATION_NET);
        $costInfo = $db->where("order_id = '{$orderId}'")
            ->field("cost")
            ->find();
        $costInfo = $db->where("order_id = '{$orderId}'")->sum("cost");
        $check = number_format($costInfo, 2) . "元";
        $total = $order['reg_fee'] + $check + $orderInfo['medical_card_price'] + $orderInfo['medical_book_price'];
        $total = number_format($total, 2) . "元";
        $cost=[
            'total'=>$total,
            'check'=>$check
        ];
        $this->assign('cost', $cost);

        ///支付方式
        $pay = [
            'pay_no'    => ($orderInfo['pay_no']==0 || in_array($orderInfo['pay_state'],[1,2,9])) ? '' : $orderInfo['pay_no'],
            'payment'   => in_array($orderInfo['pay_state'],[1,9]) || $orderInfo['pay_type']==5 ? '线下支付' : '线上支付',
            'pay_dt'    => (in_array($orderInfo['pay_state'],[1,9]) || '') ? '': $orderInfo['pay_dt'],
        ];
        switch($orderInfo['pay_state']){
            case 3:
                $pay['pay_state'] = "支付中";
                break;
            case 4:
                $pay['pay_state'] = "支付失败";
                break;
            case 5:
                $pay['pay_state'] = "退款中";
                break;
            case 6:
                $pay['pay_state'] = "退款成功";
                break;
            case 7:
                $pay['pay_state'] = "退款失败";
                break;
            case 8:
                $pay['pay_state'] = "退款审批未通过";
                break;
            case 0:
                $pay['pay_state'] = "已支付";
                break;
            default:
                $pay['pay_state'] = "";
                break;
        }
        if(!is_array($orderInfo['pay_state'],[1,2,9]) && $orderInfo['pay_type'] != 5){
            switch($orderInfo['pay_type']){
                case 1:
                    $pay['pay_type'] = "支付宝";
                    break;
                case 2:
                    $pay['pay_type'] = "微信-APP";
                    break;
                case 3:
                    $pay['pay_type'] = "银联";
                    break;
                case 4:
                    $pay['pay_type'] = "微信-公众号";
                    break;
                default:
                    $pay['pay_type'] = "";
                    break;
            }
        }else{
            $pay['pay_type'] = "";
        }
        if($orderInfo['pay_state'] > 4 && $orderInfo['pay_state'] < 9){
            $db = M('t_registration_refund_apply',null,DB_REGISTRATION_NET);
            $refund = $db->where("order_id = '{$orderId}'")->find();
            $refund['busi_approve_dt'] = strtotime($refund['busi_approve_dt']) == 0 ? '' : $refund['busi_approve_dt'];
            $refund['fina_approve_dt'] = strtotime($refund['fina_approve_dt']) == 0 ? '' : $refund['fina_approve_dt'];
            $this->assign('refund',$refund);
            switch($refund['apply_status']){
                case 1:
                    $pay['pay_state'] = "待业务审批";
                    break;
                case 2:
                    $pay['pay_state'] = "待财务审批";
                    break;
                case 3:
                    $pay['pay_state'] = "业务审批失败";
                    break;
                case 4:
                    $pay['pay_state'] = "财务审批失败";
                    break;
                case 5:
                    $pay['pay_state'] = "待财务审批";
                    break;
                default:
                    break;
            }

            if($orderInfo['pay_type'] == 1) {
                if (in_array($refund['apply_status'], [5, 6])) {
                    $pay['surplus'] = -round(($orderInfo['reg_fee'] + $check + $orderInfo['medical_card_price'] + $orderInfo['medical_book_price']+ $orderInfo['service_fee']) * 0.006, 2);
                } else {
                    $pay['surplus'] = $orderInfo['service_fee'] - round(($orderInfo['reg_fee'] + $check + $orderInfo['medical_card_price'] + $orderInfo['medical_book_price'] + $orderInfo['service_fee']) * 0.006, 2);
                }
            }
        }elseif($orderInfo['pay_state'] == 0 && $orderInfo['pay_type'] == 1){
            $pay['surplus'] = $orderInfo['service_fee']-round(($orderInfo['reg_fee'] + $check + $orderInfo['medical_card_price'] + $orderInfo['medical_book_price'] + $orderInfo['service_fee'])*0.006,2);
        }
        $pay['surplus'] = number_format($pay['surplus'],2)."元";
        $this->assign('pay',$pay);
        $this->display('register_order_info');
    }

    /**
     * 业务审核
     */
    public function refundBusinessAudit(){
        parent::getList();
        $refundId = $this->refund_id;
        $data = [
            'apply_id'          => $refundId,
            'apply_status'      => 2,
            'last_upd_dt'       => date('Y-m-d H:i:s'),
            'busi_approve_dt'   => date('Y-m-d H:i:s')
        ];
        $admin = $this->getLoginParam();
        $reason = $admin['admin_name'].' 同意退款';
        $data['busi_approve_person'] = $this->_adminId;
        $data['busi_approve_reason'] = "业务 ".$reason;
        $result = $this->_updateRefundState($data);
        $this->ajaxReturn($result);
    }

    /**
     * 修改退信审核信息
     * @param $params
     * @return bool
     */
    private function _updateRefundState($params){
        $this->addAuditLog($params['apply_id']);
        $db = M('t_registration_refund_apply',null,DB_REGISTRATION_NET);
        $result = $db->save($params);
        $this->logWrite("refund audit info:".json_encode($params),$this->_adminId,'refund');
        return $result;
    }

    /**
     * 拒绝退款
     */
    public function refundAudit(){
        parent::getList();
        $refundId = $this->refund_id;
        $orderId = $this->order_id;
        $state = $this->state;
        $data = [
            'apply_id'  => $refundId,
            'apply_status'  => $state,
            'last_upd_dt'   => date('Y-m-d H:i:s')
        ];
        $admin = $this->getLoginParam();
        $reason = $admin['admin_name'].' 拒绝退款：'.$this->reason;
        if($state == 3 ){
            $data['busi_approve_dt'] = date('Y-m-d H:i:s');
            $data['busi_approve_person'] = $this->_adminId;
            $data['busi_approve_reason'] = "业务 ".$reason;
        }elseif($state == 4){
            $data['fina_approve_person'] = $this->_adminId;
            $data['fina_approve_dt'] = date('Y-m-d H:i:s');
            $data['fina_approve_reason'] = "财务 ".$reason;
        }else{
            $this->ajaxReturn(0);
            return;
        }
        $result = $this->_updateRefundState($data);
        if($result) {
            $db = M('t_registration_order_info', null, DB_REGISTRATION_NET);
            $data = [
                'order_id'  => $orderId,
                'reason'    => $this->reason,
                'pay_state' => 8
            ];
            $result = $db->save($data);
        }

        //推送审核失败通知
        if($result){
            $res = $this->getOrderInfoById($orderId);
            if(!empty($res)){
                Push::sendOnlyOneNotice($res[0]['user_id'],"退款审核不通过","您的预约挂号已于{$res[0]['shift_date']}就诊，退款申请审核不通过；",401,$orderId);
            }
        }
        $this->ajaxReturn($result);
    }

    /**
     * 支付宝请求退款
     */
    public function orderAliPayRefund(){
        parent::getList();
        $orderId = $this->order_id;
        $refundId = $this->refund_id;
        $tradeId = $this->pay_no;
        if(IS_POST){
            $this->_refundFinancialAudit(5,$refundId);
            $refund = $this->_refundRequest(1,$orderId,$refundId,$tradeId);
            $this->ajaxReturn($refund);
            return;
        }
        $this->assign('order_id',$orderId);
        $this->assign('refund_id',$refundId);
        $this->assign('pay_no',$tradeId);
        $this->display('order_alipay_refund');
    }

    /**
     * 请求退款
     * @param $payType
     * @param $orderId
     * @param $refundId
     * @param $tradeId
     * @return null
     */
    private function _refundRequest($payType,$orderId,$refundId,$tradeId){
        $result = payment::requestForRefund($orderId,$tradeId,$refundId,$payType,'用户取消预约挂号');
        $this->logWrite("refund request pay_type:{$payType} order_id:{$orderId} refund_id:{$refundId} trade_id:{$tradeId} result:".json_encode($result),$this->_adminId,'refund');
        return isset($result['code']) && $result['code'] == 0 ? $result['refund'] : null;
    }

    /**
     * 财务审批
     */
    public function refundFinancialAudit(){
        parent::getList();
        $refundId = $this->refund_id;
        $result = $this->_refundFinancialAudit(6,$refundId);
        $this->ajaxReturn($result);
    }

    /**
     * 财务审批
     * @param $state
     * @param $refundId
     * @return bool|void
     */
    public function _refundFinancialAudit($state,$refundId){
        parent::getList();
        if(!in_array($state,[5,6])) {
            $this->ajaxReturn(0);
            return;
        }
        $data = [
            'apply_id'          => $refundId,
            'apply_status'      => $state,
            'last_upd_dt'       => date('Y-m-d H:i:s'),
        ];
        if($state == 5) {
            $admin = $this->getLoginParam();
            $reason = $admin['admin_name'] . ' 同意退款';
            $data['fina_approve_person'] = $this->_adminId;
            $data['fina_approve_reason'] = "财务 " . $reason;
            $data['fina_approve_dt']     = date('Y-m-d H:i:s');
        }
        $result = $this->_updateRefundState($data);
        return $result;
    }
    ///微信退款
    public function orderWxPayRefund(){
        parent::getList();
        $orderId = $this->order_id;
        $refundId = $this->refund_id;
        $tradeId = $this->pay_no;
        $this->_refundFinancialAudit(5,$refundId);
        $refund = $this->_refundRequest(2,$orderId,$refundId,$tradeId);
        if(in_array($refund,['REFUNDING','REFUNDED','REFUND_AILED','REFUND_UCCESS'])){
            $this->_refundFinancialAudit(6,$refundId);
        }
        $this->ajaxReturn(1);
        return;
    }
    ///审核日志
    public function addAuditLog($refundId){
        $db = M('t_registration_refund_apply',null,DB_REGISTRATION_NET);
        $info = $db->where("apply_id = '{$refundId}'")->find();
        $dbLog = M('t_log_registration_refund_apply',null,DB_LOG_REGISTRATION_NET);
        $data = [
            'apply_id'              => $refundId,
            'order_id'              => $info['order_id'],
            'reason'                => $info['reason'],
            'apply_status'          => $info['apply_status'],
            'busi_approve_reason'   => $info['busi_approve_reason'],
            'busi_approve_dt'       => $info['busi_approve_dt'],
            'busi_approve_person'   => $info['busi_approve_person'],
            'fina_approve_reason'   => $info['fina_approve_reason'],
            'fina_approve_dt'       => $info['fina_approve_dt'],
            'fina_approve_person'   => $info['fina_approve_person'],
            'log_dt'                => date('Y-m-d H:i:s')
        ];
        return $dbLog->add($data);
    }
    ///违约记录
    public function breach(){
        $timeTo = date('Y-m-d');
        $timeFor = date("Y-m-d",strtotime("-31 day"));
        $db = M('t_registration_order_info',null,DB_REGISTRATION_NET);
        $hospital = $db->distinct('hosl_name')
            ->field('hosl_name')
            ->select();
        $this->assign('timeFor',$timeFor);
        $this->assign('timeTo',$timeTo);
        $this->assign('hospital',$hospital);
        $this->display("order_breach_list");
    }
    ///违约记录列表
    public function getOrderBreachList(){
        parent::getList();
        $where = "is_delete=0";
        
        $timeFor = isset($_POST['time_for']) ? $_POST['time_for'] : date('Y-m-d',strtotime("-31 day"));
        $timeTo = isset($_POST['time_to']) ? $_POST['time_to'] : date("Y-m-d");
        $where .= " AND insert_dt BETWEEN '{$timeFor} 00:00:00' AND '{$timeTo} 23:59:59'";
        
        $hospital = trim($this->hospital);
        $where .= " AND hosl_name LIKE '%{$hospital}%'";
        $db = M("t_user_registration_bcontract",null,DB_REGISTRATION_NET);
        $total = $db->where($where)->count();
        $list = $db->where($where)
            ->field("record_id,user_id,order_id,hosl_name,dept_name,doc_name,DATE_FORMAT(shift_date,'%Y-%m-%d') AS shift_date,remark,is_delete,insert_dt")
            ->order("insert_dt DESC,record_id ASC")
            ->page($this->page,$this->rows)
            ->select();
        foreach ($list as $key => $value){
            $list[$key]['op']="<a href='javascript:void(0);' onclick='breachDel(\"{$value['record_id']}\")'>删除</a>";
        }
        $table_data["total"] = $total;
        $table_data["rows"] = is_array($list) ? $list : [];
        $this->ajaxReturn($table_data);
    }
    //删除违约记录
    public function orderBreachDel(){
        parent::getList();
        $db = M("t_user_registration_bcontract",null,DB_REGISTRATION_NET);
        $data = [
            'record_id'     => $this->record_id,
            'is_delete'     => 1
        ];
        $result = $db->save($data);
        $this->ajaxReturn($result);
    }
    //退款报表
    public function orderRefundReports(){
        $permissions = json_encode($this->_adminLevel);
        $this->assign('timeFor',$this->_timeFor);
        $this->assign('timeTo',$this->_timeTo);
        $this->assign('permissions',$permissions);
        $this->display('order_refund_report_list');
    }
    public function getRefundReportsList(){
        $input = $_POST;
        $input['time_frame'] = isset($input['time_frame']) ? $input['time_frame'] : 4;
        $input['time_for']  = isset($input['time_for']) ? $input['time_for'] : $this->_timeFor;
        $input['time_to'] = isset($input['time_to']) ? $input['time_to'] : $this->_timeTo;
        $input['pay_state'] = isset($input['pay_state']) ? $input['pay_state'] : 10;
        $where = $this->_orderRequestWhereDeal($input);

        parent::getList();
        $page = intval($this->page) <= 0 ? 1 : $this->page;
        $for = ($page - 1)*$this->rows;
        $page = $this->rows;

        $result = $this->_getOrderListByWhere($where,$for,$page);
        $list = $result['rows'];
        $totalRecords = $result['total'];
        $table_data["total"]    = $totalRecords;
        $table_data["rows"]     = $list;
        $this->ajaxReturn($table_data);
    }
    /**
     * 获取相应报表信息
     * @param $where
     * @param $limit
     * @return mixed
     */
    private function _getOrderListByWhere($where,$start=null,$pageSize=null){
        $db = M('t_registration_refund_apply a',null,DB_REGISTRATION_NET);
        ///列表信息
        $field = [
            "b.order_id AS order_id",                   //订单ID
            "b.hosl_id AS hospital_id",                 //医院ID
            'b.hosl_name AS hospital_name',             //医院名称
            'FORMAT(b.reg_fee,2) AS reg_fee',           //医院挂号费
            'FORMAT(b.service_fee,2) AS service_fee',   //手续费
            'FORMAT(IF(b.pay_type IN(1,3),IF(b.pay_type=1,IF(b.pay_state=0 OR a.apply_status IN(1,2,3,4),b.service_fee-ROUND((b.service_fee+b.reg_fee)*0.006*100)/100,-ROUND((b.service_fee+b.reg_fee)*0.006*100)/100),0),0),2) AS surplus',//第三方手续费
            "IF(b.pay_type=5 OR b.pay_state IN(1,9),'线下支付','线上支付') AS payment",///线上线下支付方式
            "CASE b.pay_type WHEN 0 THEN '' WHEN 1 THEN '支付宝' WHEN 2 THEN '微信-APP' WHEN 3 THEN '银联' WHEN 4 THEN '微信-公众号' WHEN 5 THEN '' ELSE '' END AS pay_type",//线上支付渠道
            "IF(a.apply_status IN(1,2,3,4),CASE a.apply_status WHEN 1 THEN '待业务审批' WHEN 2 THEN '待财务审批' WHEN 3 THEN '业务审批失败' ELSE '财务审批失败' END,CASE b.pay_state WHEN 0 THEN '已支付' WHEN 1 THEN '' WHEN 2 THEN '待支付' WHEN 3 THEN '支付中' WHEN 4 THEN '支付失败' WHEN 5 THEN '退款中'  WHEN 6 THEN '已退款' WHEN 7 THEN '退款失败' WHEN 8 THEN '审核失败' WHEN 9 THEN '' ELSE '未知' END ) AS pay_state_name",//支付状态
            "b.pay_state AS pay_state",                 //支付状态码
            "CASE b.order_state WHEN 1 THEN '有效' WHEN 2 THEN '取消' WHEN 3 THEN '待应诊' WHEN 4 THEN '已完成' ELSE '未知' END AS order_state",///订单状态
            "b.pay_no AS pay_no",                       //支付流水号
            "b.pay_type AS pay_type_id",                //支付方式码
            "a.apply_id AS refund_id",                  //退款申请流水号
            "a.apply_status AS refund_state",           //退款审批状态
            "IF(a.apply_status IN(2,3),a.busi_approve_dt,IF(a.apply_status IN(4,5,6),a.fina_approve_dt,'')) AS refund_approve_dt",///最后审核时间
            "a.busi_approve_dt as busi_approve_dt",     //业务审批时间
            "a.fina_approve_dt AS fina_approve_dt",     //财务审批时间
            "b.insert_dt AS insert_dt",                 //下单时间
            "a.create_dt as refund_create_dt",          //申请退款时间
            "b.pay_dt AS pay_dt",                       //支付时间
            "a.reason AS reason",                       //退款理由
            "CONCAT_WS(' ',b.shift_date,b.time_section) AS shift_date"
        ];
        $list = $db->join("RIGHT JOIN t_registration_order_info as b ON a.order_id = b.order_id")->field($field)->where($where);
        if($pageSize && ($start === 0 || $start)){
            $list = $list->limit($start,$pageSize);
        }
        $list = $list->select();
        $field = [
            "count(*) AS cou",
            "FORMAT(SUM(IF(b.pay_type IN(0,2,4,5),0,CASE WHEN b.pay_type = 1 THEN IF(b.pay_state=0 OR a.apply_status IN(1,2,3,4),b.service_fee - ROUND((b.reg_fee+b.service_fee)*0.006*100)/100,0-ROUND((b.reg_fee+b.service_fee)*0.006*100)/100)  WHEN b.pay_type = 3 THEN IF(b.pay_state=0 OR apply_status IN(1,2,3,4),b.service_fee - ROUND((b.reg_fee+b.service_fee)*0.006*100)/100,0-ROUND((b.reg_fee+b.service_fee)*0.006*100)/100) END)),2)  AS 'surplus'",
            "FORMAT(SUM(IF(b.pay_state IN(0,5,6,7,8) AND b.pay_type <> 5,b.reg_fee,0)),2) AS reg_fee",
            "FORMAT(SUM(IF(a.apply_status IN(5,6),reg_fee,0)),2) AS refund_reg_fee",
            "FORMAT(SUM(IF(b.pay_state IN(0,5,6,7,8),b.service_fee,0)),2) AS service_fee",
            "SUM(IF(b.pay_state IN(0,5,6,7,8) AND b.pay_type <> 5,1,0)) AS total_num"
        ];
        $total = $db->join("RIGHT JOIN t_registration_order_info as b ON a.order_id = b.order_id")->field($field)->where($where)->find();

        $table_data["total"]    = $total['cou'];
        $table_data["rows"]     = (array)$list;
        $table_data['sum']      = $total;
        return $table_data;
    }
    ///导出excel
    public function orderReportDerivedExcel(){
        $input = $_GET;
        $where = $this->_orderRequestWhereDeal($input);
        $data = $this->_getOrderListByWhere($where);
        $sum = $data['sum'];
        $total = $data['total'];
        $rows = $data['rows'];
        import('@.CustomLib.Excel');
        $tableHeader = [
            ['width'=>20,'key'=>'order_id','value'=>'订单号','formant'=>PHPExcel_Style_NumberFormat::FORMAT_NUMBER],
            ['width'=>20,'key'=>'insert_dt','value'=>'下单时间','formant'=>'yyyy-mm-dd h:mm:ss'],
            ['key'=>'hospital_name','value'=>'挂号医院'],
            ['width'=>10,'key'=>'reg_fee','value'=>'挂号费(元/￥)'],
            ['width'=>10,'key'=>'shift_date','value'=>'预约时间','formant'=>PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2],
            ['width'=>10,'key'=>'payment','value'=>'支付方式'],
            ['width'=>10,'key'=>'pay_state_name','value'=>'订单状态'],
            ['width'=>20,'key'=>'pay_dt','value'=>'支付时间','formant'=>'yyyy-mm-dd h:mm:ss'],
            ['width'=>10,'key'=>'pay_type','value'=>'支付渠道'],
            ['width'=>20,'key'=>'pay_no','value'=>'商户支付流水号'],
            ['width'=>20,'key'=>'refund_create_dt','value'=>'申请退款时间','formant'=>'yyyy-mm-dd h:mm:ss'],
            ['width'=>20,'key'=>'refund_approve_dt','value'=>'审批退款时间','formant'=>'yyyy-mm-dd h:mm:ss'],
            ['width'=>10,'key'=>'surplus','value'=>'盈余（元/￥）'],
        ];
        //$this->exportExcel("guijk_order_reports",'挂号订单报表',$tableHeader,$tableArray);
        $this->exportExcelCsv('挂号订单报表','挂号订单报表',$tableHeader,$rows);
    }
    ///申请退款
    public function applyForRefund(){
        parent::getList();
        if(IS_POST){
            $db = M("t_registration_order_info",null,DB_REGISTRATION_NET);
            $order = $db->where("order_id = '{$this->order_id}'")->find();
            $request = [
                'user_id'   => $order['gjk_user_id'],
                'gender'    => $order['patient_sex'] - 1,
                'c_type'    => 2,
                'c_ver'     => 4000,
                'task_id'   => 0,
                'sid'       => 0,
                'pid'       => 0,
                'orderId'   => $this->order_id,
                'reason'    => $this->reason
            ];
            $json = json_encode($request);
            $url = rtrim(C("api_cfg.base_url"),'/')."/".trim(C("api_cfg.register"))."/order/cancel";
            $result = Common::post($url,['json'=>$json]);
            $this->logWrite("refund apply request json:{$json},result json:{$result}",$this->_adminId,'refund');
            $this->ajaxReturn(json_decode($result,true));
        }
        $this->assign("order_id",$this->order_id);
        $this->display('order_apply_for_refund');
    }
    ///获取订单Id
    public function getOrderInfoById($orderId){
        $db = M("t_registration_order_info",null,DB_REGISTRATION_NET);
        $sql = "SELECT gjk_user_id AS 'user_id',DATE_FORMAT(shift_date,'%Y年%m月%d日') AS 'shift_date' FROM t_registration_order_info WHERE order_id='{$orderId}' LIMIT 1;";
        return $db->query($sql);
    }
    ///对账报表
    public function captainReport(){
        $param = $this->getLoginParam();
        $level = explode(" ",$param['admin_level']);
        $hospitalId = [];
        foreach ($level as $value){
            $lev = 'captain_report';
            if($value == $lev){
                break;
            }
            if(strpos($value,'captain_report-hospital_id-') !== false){
                list($l,$h,$id) = explode('-',$value);
                array_push($hospitalId,$id);
            }
        }
        if (count($hospitalId)) {
            $where = "hospital_id IN('" . implode("','", $hospitalId) . "')";
        }else{
            $where = "1=1";
        }
        $table = M("t_hospital_info",null,DB_REGISTRATION_NET);
        $hospital = $table->field(['hospital_id','hospital_name'])->where($where)->select();
        if(count($hospital)>1){
            array_push($hospital,['hospital_id'=>implode(",",$hospitalId),'hospital_name'=>'全部']);
        }
        $this->assign("timeFor",date('Y-m-d',strtotime("-8 day")));
        $this->assign("timeTo",date('Y-m-d',strtotime("-1 day")));
        $hospital = array_reverse($hospital);
        $this->assign("hospital",$hospital);
        $this->display('captain_report_list');
    }
    public function getOrderCaptainReportList(){
        $captain = $_POST['captain'];
        $input = $_POST;
        $page = intval($input['page']) <= 0 ? 1 : $input['page'];
        $begin = ($page - 1) * $input['rows'];
        $page = $input['rows'];
        $limit = "LIMIT {$begin},{$page}";
        $where = $this->_orderCaptainReportListWhere($input,$captain);
        $list = $this->_orderCaptainReportListQuery($where,$limit);
        $where = $this->_orderCaptainReportListWhere($input,'0,1,2,3');
        $result = $this->_orderCaptainReportListQuery($where,'',true);
        $result[0]['captain'] = $captain;
        array_push($list,$result[0]);
        $data = [
            'total'  => ($captain == 0 ? $result[0]['no_match_num'] : $result[0]['match_num'])+1,
            'rows'   => $list
        ];
        $this->ajaxReturn($data);
    }
    ///查询
    private function _orderCaptainReportListQuery($where,$limit='',$total=false){
        $table = M("t_registration_order_info",null,DB_REGISTRATION_NET);
        ///查询数据
        $field = [
            "CASE igs_finance_rec WHEN 0 THEN '未匹配' WHEN 1 THEN '系统匹配' WHEN 2 THEN '人工匹配' WHEN 3 THEN '撤销匹配' END AS igs_finance_rec_state",
            "igs_bill_no AS order_id",
            "igs_finance_rec AS igs_finance_rec_id",
            "hosl_name AS hospital",
            "dept_name AS department",
            "expert_name AS doctor",
            "reg_fee AS reg_fee",
            //"shift_date AS shift_date",
            "CONCAT_WS(' ',shift_date,time_section) AS shift_date",
            "patient_name AS patient",
            "certificate_no AS id_card",
            "igs_finance_rec_remarks AS igs_finance_rec_remarks",
            "igs_serial_number AS igs_serial_number",
            "igs_finance_rec_dt",
            "igs_finance_rec_user"
        ];
        $field = implode(",",$field);
        ///组装sql语句
        $orderSql = "SELECT '贵健康' AS source,1 AS source_id,order_id AS id,{$field} FROM t_registration_order_info WHERE igs_finance_rec_dt IS NOT NULL AND {$where}";
        $noOrderSql = "SELECT hosl_name AS source,2 AS source_id,id,{$field} FROM t_finance_no_order WHERE {$where}";
        if($total){
            $field = [
                "SUM(IF(igs_finance_rec_id IN (0,3),1,0)) AS no_match_num",
                "SUM(IF(igs_finance_rec_id IN (1,2),1,0)) AS match_num",
                "FORMAT(SUM(IF(igs_finance_rec_id IN(0,3),reg_fee,0)),2) AS no_match",
                "FORMAT(SUM(IF(igs_finance_rec_id=1,reg_fee,0)),2) AS sys_match",
                "FORMAT(SUM(IF(igs_finance_rec_id=2,reg_fee,0)),2) AS atr_match"
            ];
            $field = implode(",", $field);
            $sql = "SELECT {$field} FROM (({$orderSql}) UNION ({$noOrderSql})) AS t_captain";
            return $table->query($sql);
        }else {
            $sql = "({$orderSql}) UNION ({$noOrderSql}) ORDER BY shift_date DESC {$limit}";
            $list = $table->query($sql);
            return (array)$list;
        }
    }

    /**
     * 财务报表条件处理
     * @param $input
     * @param $captain
     * @return array|string
     */
    private function _orderCaptainReportListWhere($input,$captain){
        $where = ["igs_finance_rec IN($captain)","shift_date >= '2016-07-14'"];
        $timeFor = date("Y-m-d H:i:s",strtotime("-7 day"));
        $timeTo = date("Y-m-d H:i:s");
        ///时间范围筛选
        if(!empty($input['time_frame'])){
            if(!empty($input['time_for']) && !empty($input['time_to'])){
                $timeFor = $input['time_for'];
                $timeTo = $input['time_to'];
            }
            switch ($input['time_frame']){
                case 2:
                    array_push($where,"shift_date BETWEEN '{$timeFor}' AND '{$timeTo}'");
                    break;
                default:
                    array_push($where,"shift_date BETWEEN '{$timeFor}' AND '{$timeTo}'");
            }
        }else{
            array_push($where,"shift_date BETWEEN '{$timeFor}' AND '{$timeTo}'");
        }
        ///匹配状态
        if(strlen($input['match_state'])>0 && $captain != "0,1,2,3"){
            array_push($where,"igs_finance_rec IN({$input['match_state']})");
        }
        ///医院
        if(!empty($input['hospital'])){
            array_push($where,"hosl_id IN({$input['hospital']})");
        }
        $where = implode(" AND ",$where);
        return $where;
    }

    /**
     * 人工匹配
     */
    public function captainArtificialMatching(){
        $input = $_POST;
        $param = $this->getLoginParam();
        $data = [
            "igs_serial_number"         => $input['igsSerialNumber'],
            'igs_finance_rec'           => $input['igsFinanceRec'],
            'igs_finance_rec_dt'        => date("Y-m-d H:i:s"),
            'igs_finance_rec_remarks'   => $input['remark'],
            'igs_finance_rec_user'      => $param['admin_name']."({$param['user_name']})",
        ];
        $where = ["igs_serial_number"=>$input['igsSerialNumber']];
        $field = [
            "hosl_id",
            "hosl_name",
            "dept_id",
            "dept_name",
            "expert_id",
            "expert_name",
            "shift_date",
            "time_section",
            "patient_name",
            "certificate_no",
            "reg_fee",
            "igs_serial_number",
            "igs_bill_no",
            "igs_finance_rec",
            "igs_finance_rec_remarks",
            "igs_finance_rec_dt",
            "igs_finance_rec_user",
            "insert_dt"
        ];
        if($input['source']==1){
            $table = M("t_registration_order_info",null,DB_REGISTRATION_NET);
            $info = $table->where($where)->field($field)->find();
            $result = $table->where($where)->save($data);
        }else{
            $table = M("t_finance_no_order",null,DB_REGISTRATION_NET);
            $info = $table->where($where)->field($field)->find();
            $result = $table->where($where)->save($data);
        }
        $table = M("t_log_finance_no_order",null,DB_LOG_REGISTRATION_NET);
        $info["log_dt"] = date("Y-m-d H:i:s");
        $info["client_ip"] = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
        $table->add($info);
        $this->ajaxReturn($result);
    }

    public function orderCaptainReportDerivedExcel(){
        $input = $_GET;
        $screen = [];
        switch ($input['time_frame']){
            case 2:
                $screen[] = "预约时间：{$input['time_for']}至{$input['time_to']}";
                break;
            default:
                break;
        }
        switch (strval($input['match_state'])){
            case "":
                $screen[] = "匹配状态：全部";
                break;
            case "0,3":
                $screen[] = "匹配状态：未匹配";
                break;
            case "1":
                $screen[] = "匹配状态：系统匹配";
                break;
            case "2":
                $screen[] = "匹配状态：人工匹配";
                break;
        }
        if(strlen($input['hospital']) > 0){
            $table = M("t_hospital_info",null,DB_REGISTRATION_NET);
            $hospital = $table->field(['hospital_id','hospital_name'])->where("hospital_id IN({$input['hospital']})")->select();
            $hosp = [];
            foreach ($hospital as $value){
                $hosp[] = $value['hospital_name'];
            }
            $screen[] = "挂号医院：".implode(",",$hosp);
        }else{
            $screen[] = "挂号医院：全部";
        }
        ///已匹配
        $where = $this->_orderCaptainReportListWhere($input,'1,2');
        $result = $this->_orderCaptainReportListQuery($where);
        $this->logWrite("对账报表-已匹配:".json_encode($result),120,"export_excel");
        $head = [
            "igs_finance_rec_state"     => "匹配状态",
            "order_id"                  => "订单号",
            "hospital"                  => "挂号医院",
            "department"                => "挂号科室",
            "doctor"                    => "挂号医生",
            "reg_fee"                   => "挂号费(元/￥)",
            "shift_date"                => "预约时间",
            "patient"                   => "就诊人",
            "id_card"                   => "身份证",
            "igs_finance_rec_dt"        => "操作时间",
            "igs_finance_rec_user"      => "操作人",
            "igs_finance_rec_remarks"   => "备注"
        ];
        $matchFileName = $this->saveExcelCsv("已匹配","对账报表-已匹配数据",$head,$result,null,$screen);
        ///未匹配
        $where = $this->_orderCaptainReportListWhere($input,'0,3');
        $result = $this->_orderCaptainReportListQuery($where);
        $this->logWrite("对账报表-未匹配:".json_encode($result),120,"export_excel");
        $head = [
            "igs_finance_rec_state"     => "匹配状态",
            "source"                    => "数据来源",
            "order_id"                  => "订单号",
            "hospital"                  => "挂号医院",
            "department"                => "挂号科室",
            "doctor"                    => "挂号医生",
            "reg_fee"                   => "挂号费(元/￥)",
            "shift_date"                => "预约时间",
            "patient"                   => "就诊人",
            "id_card"                   => "身份证",
            "igs_finance_rec_dt"        => "操作时间",
            "igs_finance_rec_user"      => "操作人",
            "igs_finance_rec_remarks"   => "备注"
        ];
        $dir = strtr($matchFileName,[$this->mbConvertEncoding('已匹配.csv')=>""]);
        $noMatchFileName = $this->saveExcelCsv("未匹配","对账报表-未匹配数据",$head,$result,$dir,$screen);
        if(strlen($input['match_state']) == 0){
            $file = [$matchFileName,$noMatchFileName];
        }elseif ($input['match_state'] == 0){
            $file = [$noMatchFileName];
        }else{
            $file = [$matchFileName];
        }
        $this->compressedFile("对账报表",$file,$dir);
        @unlink($noMatchFileName);
        @unlink($matchFileName);
        @rmdir($dir);
    }
    public function captainArtificialMatchingLog(){
        parent::getList();
        $igsSerialNumber = $this->igsSerialNumber;
        $source = $this->source;
        $where = [
            'igs_serial_number'     => $igsSerialNumber,
        ];
        $field = [
            "CASE igs_finance_rec WHEN 0 THEN '未匹配' WHEN 1 THEN '系统匹配' WHEN 2 THEN '人工匹配' WHEN 3 THEN '撤销匹配' END AS igs_finance_rec_state",
            'igs_finance_rec',
            "igs_finance_rec_remarks",
            "IF(igs_finance_rec_dt IS NULL,insert_dt,igs_finance_rec_dt) AS igs_finance_rec_dt",
            "IF(igs_finance_rec_user IS NULL OR igs_finance_rec_user = '','system',igs_finance_rec_user) AS igs_finance_rec_user",
            "insert_dt"
        ];
        if($source == 1){
            $table = M("t_registration_order_info",null,DB_REGISTRATION_NET);
            $info = $table->where($where)->field($field)->find();
        }else{
            $table = M("t_finance_no_order",null,DB_REGISTRATION_NET);
            $info = $table->where($where)->field($field)->find();
        }
        $table = M("t_log_finance_no_order",null,DB_LOG_REGISTRATION_NET);
        $logList = $table->where($where)->field($field)->order("log_dt DESC")->select();
        $this->ajaxReturn(['list'=>$info,'log'=>(array)$logList]);
    }
    public function captainReportExport(){
        if(IS_POST){
            $input = $_POST;
            $where = [
                "pay_state = 0",
                "a.pay_type <> 5",
                "a.shift_date BETWEEN '{$input['time_for']} 00:00:00' AND '{$input['time_to']} 23:59:59'",
                "a.posting_dt <> 0",
                "a.shift_date >= '2016-07-14'"
            ];
            if(strlen($input['hospital'])>0){
                array_push($where,"a.hosl_id IN({$input['hospital']})");
            }
            $field = [
                "a.certificate_no",
                "'挂号' AS type_name",
                "a.patient_name",
                "CASE a.patient_sex WHEN 1 THEN '男' WHEN 2 THEN '女' ELSE '未知' END AS patient_sex",
                "a.igs_bill_no",
                "a.reg_fee",
                "IF(a.posting_dt IS NULL,a.pay_dt,a.posting_dt) AS posting_dt",
                "1 AS pay_state",
                "a.igs_serial_number",
                "a.pay_no",
                "b.igs_online_terminal_no",
                "'贵阳朗玛' AS cooperation_units",
                "5 AS pay_type"
            ];
            $table = M("t_registration_order_info a",null,DB_REGISTRATION_NET);
            $list = $table->join("t_hospital_info AS b ON b.hospital_id = a.hosl_id")->where($where)->field($field)->select();
            $head = [
                "{$input['time_for']} 00:00:00",
                "{$input['time_to']} 23:59:59",
                "贵阳朗玛",
                intval($table->where($where)->count()),
                intval($table->where($where)->sum("reg_fee")),
            ];
            $this->exportExcelTxt(date("Ymd",strtotime($input['time_for']))."_".date("Ymd",strtotime($input['time_to'])),$head,$list);
        }
        $this->assign("timeFor",date('Y-m-d',strtotime("-8 day")));
        $this->assign("timeTo",date('Y-m-d',strtotime("-1 day")));
        $this->display('captain_report_derived_list');
    }
    public function captainReportHospitalExportList(){
        $param = $this->getLoginParam();
        $level = explode(" ",$param['admin_level']);
        $hospitalId = [];
        foreach ($level as $value){
            $lev = 'captain_report';
            if($value == $lev){
                break;
            }
            if(strpos($value,'captain_report-hospital_id-') !== false){
                list($l,$h,$id) = explode('-',$value);
                array_push($hospitalId,$id);
            }
        }
        if (count($hospitalId)) {
            $where = "hospital_id IN('" . implode("','", $hospitalId) . "')";
        }else{
            $where = "1=1";
        }
        $table = M("t_hospital_info",null,DB_REGISTRATION_NET);
        $hospital = $table->field(['hospital_id','hospital_name'])->where($where)->select();
        $this->ajaxReturn($hospital);
    }
}