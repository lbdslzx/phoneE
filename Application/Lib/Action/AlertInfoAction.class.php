<?php

/**
 *
 * 弹出框class
 * smy
 */

class AlertInfoAction extends CommonAction{

    public function index(){
    }

    /**
     *
     * 订单详情弹出框
     */
    public function order_view(){
        $db = M("t_registration_order_info",null,"DB_QUERY");
        $order_id = I(2);
        $data = $db->where("order_id = $order_id")->find();
        if(empty($data)){
            $data["order_id"] = "该订单不存在";
        }else{
            $data['patient_sex'] = $data['patient_sex'] == 1 ? '男' : '女';
            $data['shift_date'] = $data['shift_date'].chn_time_range($data['time_range']);
            $data['widgetId'] = chn_widget_id($data['widgetId']);
        }
        $this->assign($data);
        $this->display();
    }
}