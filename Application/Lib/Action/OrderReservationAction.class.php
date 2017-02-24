<?php

/**
 *
 * 预约挂号：
 * smy
 */

class OrderReservationAction extends CommonAction{

    public $table_name = "t_registration_order_info";
    public $db_name = DB_QUERY;


    /**
     *
     * 贵航300
     */
    public function gh300_index(){
        $table_title = array(
            "order_id"	=> "订单ID",
            "hosl_name"	=> "就诊医院",
            "dept_name"	=> "就诊科室",
            "expert_name" => '就诊医生',
            "clinic_name" => '门诊类型',
            "shift_date"	=> "就诊时间",
            "patient_name"	=> "就诊人",
            "patient_phone"	=> "手机号",
            "order_state"   => "订单状态",
        );

        $table = M($this->table_name,null,$this->db_name);
        $begin_time =  $table->field("min(insert_dt)")->select();
        $this->assign("begin_time",empty($begin_time) ? time() : strtotime($begin_time[0]['min(insert_dt)']));
        $this->assign("table_title",$table_title);
        $this->display();
    }
    private function _orderState($key){
        $state = [
            0  => '以创建',
            2  => '已取消',
            10 => '病人已核对',
            20 => '导医已核实办卡',
            30 => '核实办卡后已短信提醒',
            40 => '就诊前已短信提醒',
            50 => '就诊已完成'
        ];
        return $state[$key] ? $state[$key] : '';
    }

    /**
     *
     * 贵航300获取订单
     */
    public function gh300_getList(){
        parent::getList();
        $table = M($this->table_name,null,$this->db_name);
        $where = "hosl_id = '0a078eaf-f709-4219-bad0-0715501bf1f7'";
        if(!empty($this->end_dt) && !empty($this->begin_dt)){
            $where = $where." and insert_dt >= '{$this->begin_dt}' and insert_dt <= '{$this->end_dt}'";
        }
        $data = $table->where($where)->page($this->page,$this->rows)->order("insert_dt DESC")->select();
        $totalRecords = $table->where($where)->count();
        foreach ($data as $k=>$v){
            $data[$k]['order_state'] = $this->_orderState($v['order_state']);
            $data[$k]['shift_date'] = $data[$k]['shift_date'].chn_time_range($data[$k]['time_range']);
            $data[$k]["order_id"] = '<a href="javascript:void(0);" onclick="view_order_info('.$data[$k]["order_id"].');" class="easyui-linkbutton" iconCls="icon-cancel" style="text-decoration: none;" title="点击查看订单详情" plain="true">'.$data[$k]["order_id"].'</a>';
        }
        $table_data["total"] = $totalRecords;
        $table_data["rows"] = empty($data)?array():$data;
        $this->ajaxReturn($table_data);
    }

    /**
     *
     * 贵航300导出
     */
    public function gh300_export(){
        import('@.CustomLib.Excel');
        $begin_dt = I(3);
        $end_dt = I(4);
        $table = M($this->table_name,null,$this->db_name);
        $where = "hosl_id = '0a078eaf-f709-4219-bad0-0715501bf1f7'";
        if(!empty($end_dt) && !empty($begin_dt)){
            $where = $where." and insert_dt >= '{$begin_dt}' and insert_dt <= '{$end_dt}'";
        }
        $data = $table->where($where)->field('order_id,hosl_name,dept_name,expert_name,clinic_name,shift_date,patient_name,patient_sex,patient_age,certificate_no,widgetId,widgetValue,patient_address,order_state')->order("insert_dt DESC")->select();
        foreach($data as $key => $value){
            $data[$key]['shift_date'] = $data[$key]['shift_date'].chn_time_range($data[$key]['time_range']);
            $data[$key]['patient_sex'] = $data[$key]['patient_sex'] == 1 ? '男' : '女';
            $data[$key]['shift_date'] = $data[$key]['shift_date'].chn_time_range($data[$key]['time_range']);
            $data[$key]['widgetId'] = chn_widget_id($data[$key]['widgetId']);
            $data[$key]['order_state'] = $this->_orderState($value['order_state']);
        }
        $tableheader = array('订单ID','就诊医院','就诊科室','就诊医生','门诊类型','就诊时间','就诊人','性别','年龄','身份证','就诊卡类型','卡号','地址','订单状态');
        $class = new Excel();
        $class->export($tableheader,$data,'gh300_'.time());
    }

    /**
     *
     * 挂号网订单
     */
    public function registrationNet_index(){
        $table_title = array(
            "order_id"	=> "订单ID",
            "hosl_name"	=> "就诊医院",
            "dept_name"	=> "就诊科室",
            "expert_name" => '就诊医生',
            "clinic_name" => '门诊类型',
            "shift_date"	=> "就诊时间",
            "patient_name"	=> "就诊人",
            "patient_phone"	=> "手机号",
        );
        $table = M('t_registration_hospital_info',null,$this->db_name);
        $hospital = $table->field("hosl_name, hosl_id")->select();
        foreach($hospital as $v){
            $hospital_id[$v['hosl_id']] = $v['hosl_name'];
        }
        $table = M($this->table_name,null,$this->db_name);
        $begin_time =  $table->field("min(insert_dt)")->select();
        $this->assign("begin_time",empty($begin_time) ? time() : strtotime($begin_time[0]['min(insert_dt)']));
        $this->assign("table_title",$table_title);
        $this->assign("hospitals",$hospital_id);
        $this->display();
    }

    /**
     *
     * 挂号网获取订单
     */
    public function registrationNet_getList(){
        parent::getList();
        $table = M($this->table_name,null,$this->db_name);
        if($this->hospital_id != -1){
            $where = "hosl_id = '{$this->hospital_id}'";
        }else{
            $where = "hosl_id <> '0a078eaf-f709-4219-bad0-0715501bf1f7'";
        }
        if(!empty($this->end_dt) && !empty($this->begin_dt)){
            $where = $where." and insert_dt >= '{$this->begin_dt}' and insert_dt <= '{$this->end_dt}'";
        }
        $data = $table->where($where)->page($this->page,$this->rows)->order("insert_dt DESC")->select();
        $totalRecords = $table->where($where)->count();
        foreach ($data as $k=>$v){
            $data[$k]['shift_date'] = $data[$k]['shift_date'].chn_time_range($data[$k]['time_range']);
            $data[$k]["order_id"] = '<a href="javascript:void(0);" onclick="view_order_info('.$data[$k]["order_id"].');" class="easyui-linkbutton" iconCls="icon-cancel" style="text-decoration: none;" title="点击查看订单详情" plain="true">'.$data[$k]["order_id"].'</a>';
        }
        $table_data["total"] = $totalRecords;
        $table_data["rows"] = empty($data)?array():$data;
        $this->ajaxReturn($table_data);
    }

    /**
     *
     * 挂号网导出
     */
    public function registrationNet_export(){
        import('@.CustomLib.Excel');
        $begin_dt = I(3);
        $end_dt = I(4);
        $hospital_id = I(5);
        $table = M($this->table_name,null,$this->db_name);
        if($hospital_id == -1){
            $where = "hosl_id <> '0a078eaf-f709-4219-bad0-0715501bf1f7'";
        }else{
            $where = "hosl_id = '{$this->hospital_id}'";
        }
        if(!empty($end_dt) && !empty($begin_dt)){
            $where = $where." and insert_dt >= '{$begin_dt}' and insert_dt <= '{$end_dt}'";
        }
        $data = $table->where($where)->field('order_id,hosl_name,dept_name,expert_name,clinic_name,shift_date,patient_name,patient_sex,patient_age,certificate_no,widgetId,widgetValue,patient_address')->order("insert_dt DESC")->select();
        foreach($data as $key => $value){
            $data[$key]['shift_date'] = $data[$key]['shift_date'].chn_time_range($data[$key]['time_range']);
            $data[$key]['patient_sex'] = $data[$key]['patient_sex'] == 1 ? '男' : '女';
            $data[$key]['shift_date'] = $data[$key]['shift_date'].chn_time_range($data[$key]['time_range']);
            $data[$key]['widgetId'] = chn_widget_id($data[$key]['widgetId']);
        }
        $tableheader = array('订单ID','就诊医院','就诊科室','就诊医生','门诊类型','就诊时间','就诊人','性别','年龄','身份证','就诊卡类型','卡号','地址');
        $class = new Excel();
        $class->export($tableheader,$data,'registrationNet_'.time());
    }


}