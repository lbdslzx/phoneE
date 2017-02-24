<?php
/**
 * Created by PhpStorm.
 * User: daxin.yang@longmaster.com.cn<daxin.yang@longmaster.com.cn>
 * Date: 2015/12/19
 * Time: 11:07
 * 功能:
 */
class InquiryAction extends CommonAction{

    function getList(){
        parent::getList();
        $db = M("t_coupons_cfg",null,DB_UC);

        $where = "" ;

        if(!empty($this->get_type) && $this->get_type > 0){
            $where .= "get_type = {$this->get_type}";
        }

        if($this->per_limit !== false && $this->per_limit >= 0){
            $where .= "per_limit = {$this->per_limit}";
        }

        $field = array(
            'coupons_id',
            'coupons_name',
            'coupons_desc',
            'free_coin',
            "CASE limit_num WHEN 100000000 THEN '无限次' ELSE limit_num END as limit_num",
            "CASE get_num WHEN 100000000 THEN '无限次' ELSE get_num END as get_num",
            "CASE get_type WHEN 1 THEN '首次领取' WHEN 2 THEN '每日领取' WHEN 3 THEN '每月领取' END as get_type",
            'valid_dt',
            'per_limit',
            "CASE per_limit WHEN 0 THEN '贵健康' WHEN 1 THEN 'IVR' WHEN 2 THEN '全国版VIP' WHEN 3 THEN '全国版非VIP' END as per_limit_desc",
            "DATE_FORMAT(limit_begin_dt,'%Y/%m/%d %H:%i:%s') as limit_begin_dt",
            "DATE_FORMAT(limit_end_dt,'%Y/%m/%d %H:%i:%s') as limit_end_dt",
            "DATE_FORMAT(get_begin_dt,'%Y/%m/%d %H:%i:%s') as get_begin_dt",
            "DATE_FORMAT(get_end_dt,'%Y/%m/%d %H:%i:%s') as get_end_dt",
            "DATE_FORMAT(insert_dt,'%Y/%m/%d %H:%i:%s') as insert_dt",
        );

        $data = $db->field($field)->where($where)->order('insert_dt DESC,coupons_id DESC')->select();

        $list = array();
        $time = time();
        foreach($data as $key=>$val){
            $action = "";
            if($time < strtotime($val['limit_begin_dt']) && $time < strtotime($val['get_begin_dt'])){
                $action .= "<a href='cardEdit?coupons_id={$val['coupons_id']}&per_limit={$val['per_limit']}'>编辑</a>&nbsp;<a onclick='card_delete(\"{$val['coupons_id']}\",\"{$val['per_limit']}\")' href='javascript:void(0);'>删除</a>";
            }
            $val['action'] = $action;
            $list[] = $val;
        }

        if(empty($list)){
            $list = array();
        }

        $totalRecords = count($list);

        $table_data["total"] = $totalRecords;
        $table_data["rows"] = $list;
        $this->ajaxReturn($table_data);
    }
    function cardEdit2(){
        parent::getList();
        $table = M("t_coupons_cfg",null,DB_UC);
        $couponsId = $_GET['coupons_id'];
        $perLimit = $_GET['per_limit'];
        if(!empty($couponsId)){
            $where = [
                "coupons_id = {$couponsId}", 
                "per_limit = {$perLimit}"
            ];
            $detail = $table->where($where)->find();
            $this->assign("detail",$detail);
        }
        $this->display("card_cfg_edit");
    }
    function cardEdit(){
        $db = M("t_coupons_cfg",null,DB_UC);
        if(IS_POST){//修改
            $data = $_POST;

            if($data['check_limit_num'] == 1 && !isset($data['limit_num'])){
                $data['limit_num'] = '100000000';
            }

            if($data['check_get_num'] == 1 && !isset($data['get_num'])){
                $data['get_num'] = '100000000';
            }
            unset($data['check_limit_num']);
            unset($data['check_get_num']);

            if(empty($data['coupons_id'])){
                $max = $db->max('coupons_id');
                $data['coupons_id'] = $max + 1;
                $data['insert_dt'] = date('Y-m-d H:i:s');
                $result = $db->add($data);
            }else{
                $sql = $this->orgUpdSql('t_coupons_cfg',$data);
                $result = $db->execute($sql);
            }
            $this->ajaxReturn($result);
        }else{//新增
            $coupons_id = $_GET['coupons_id'];
            $per_limit = $_GET['per_limit'];
            if(!empty($coupons_id)){
                $where = "coupons_id = {$coupons_id} AND per_limit = {$per_limit}";
                $detail = $db->where($where)->select();
                $this->assign("detail",$detail[0]);
            }else{
                $max = $db->max('coupons_id');
                $this->assign("coupons_id",$max + 1);
            }
        }
        $this->display();
    }

    function cardDelete(){
        $db = M("t_coupons_cfg",null,DB_UC);
        $coupons_id = $_POST['coupons_id'];
        $per_limit = $_POST['per_limit'];
        $where = "coupons_id = {$coupons_id} and per_limit={$per_limit}";
        $result = $db->where($where)->delete();
        $this->ajaxReturn($result);
    }

    function orgUpdSql($table,$data){
        $w = "";
        foreach($data as $key=>$val){
            $w .= ", {$key}='$val'";
        }
        $w = ' SET '. ltrim($w,',');
        $where = " where coupons_id = {$data['coupons_id']} and per_limit = {$data['per_limit']}";
        return "UPDATE {$table} ".$w.$where;
    }
    ///问诊量
    public function interrogation(){
        $province = file_get_contents(RESOURCE_PUBLIC."json/Province.json");
        $this->assign("provinceJson",$province);
        $this->assign("province",json_decode($province,true));
        $this->assign("timeFor",date("Y-m-d H:i:s",strtotime("-7 day")));
        $this->assign("timeTo",date('Y-m-d H:i:s'));
        $this->display("inquiry_interrogation_list");
    }
    public function getInterrogationList(){
        parent::getList();
        $input = $_POST;
        $where = "a.begin_dt <> a.end_dt AND a.begin_dt <> 0 AND a.end_dt <> 0";
        if(isset($input['inquiry_type']) && in_array($input['inquiry_type'],[1,2])){
            $where .= " AND a.doc_type = {$input['inquiry_type']}";
        }
        if(isset($input['version']) && in_array($input['version'],[1,2])){
            if($input['version'] == 1){
                $where .= " AND b.province_id IN (0,1)";
            }else{
                $where .= " AND b.province_id NOT IN (0,1)";
            }
        }
        if(isset($input['inquiry_id']) && !empty(trim($input['inquiry_id']))){
            $inquiryId = trim($input['inquiry_id']);
            $where .= " AND a.inquiry_id LIKE '%{$inquiryId}%'";
        }
        if(isset($input['phone_number']) && !empty(trim($input['phone_number']))){
            $phoneNumber = trim($input['phone_number']);
            $where .= " AND b.phone_num LIKE '%{$phoneNumber}%'";
        }
        if(isset($input['user_province']) && $input['user_province'] > 0){
            $where .= " AND b.province_id = {$input['user_province']}";
        }
        if(isset($input['doc_province']) && $input['doc_province'] > 0){
            $where .= " AND c.province_id = {$input['user_province']}";
        }
        if(isset($input['doc_id']) && !empty($input['doc_id'])){
            $where .= " AND c.doc_id = '{$input['doc_id']}'";
        }
        if(isset($input['doc_name']) && !empty($input['doc_name'])){
            $where .= " AND c.doc_name LIKE '%{$input['doc_name']}%";
        }
        if(isset($input['doc_phone']) && !empty($input['doc_phone'])){
            $where .= " AND c.phone_num LIK '%{$input['doc_phone']}%'";
        }
        $timeFor = isset($input['time_for']) && $input['time_for'] ? $input['time_for'] : date("Y-m-d H:i:s",strtotime("-7 day"));
        $timeTo = isset($input['time_to']) && $input['time_to'] ? $input['time_to'] : date("Y-m-d H:i:s");
        $where .= " AND a.begin_dt BETWEEN '{$timeFor}' AND '{$timeTo}'";
        $page = $this->page == 0 ? 1 : $this->page;
        $rows = $this->rows;
        $begin = ($page-1)*$rows;
        $sql = "SELECT
        a.inquiry_id AS inquiry_id,
        a.begin_dt AS inquiry_dt,
        a.doc_name AS doctor,
        REPLACE(b.phone_num,'+86','') AS phone_number,IF(a.doc_type=1,'视频问诊','电话问诊') AS inquiry_type,
        IF(b.province_id IN(0,1),'贵州版','全国版') AS version,
        b.province_id,
        c.doc_id,
        c.province_id AS doc_pro,
        c.phone_num AS doc_phone_number,
        UNIX_TIMESTAMP(a.end_dt)-UNIX_TIMESTAMP(a.begin_dt) AS inquiry_length,
        UNIX_TIMESTAMP(a.begin_dt)-UNIX_TIMESTAMP(a.insert_dt) AS call_time
        FROM t_user_inquiry_record AS a 
        JOIN t_user_phone AS b ON a.patient_id = b.user_id 
        JOIN t_doctor_info AS c ON a.doc_id = c.doc_id
        WHERE {$where} 
        ORDER BY a.begin_dt DESC,a.doc_type ASC,a.inquiry_id ASC 
        LIMIT {$begin},{$rows}";
        $table = M("t_user_inquiry_record a",null,DB_QUERY);
        $data = $table->query($sql);
        $sql = "SELECT COUNT(inquiry_id) AS coun FROM t_user_inquiry_record AS a JOIN t_user_phone AS b ON a.patient_id = b.user_id JOIN t_doctor_info AS c ON a.doc_id = c.doc_id WHERE {$where}";
        $total = $table->query($sql);
        $table_data["total"] = $total[0]['coun']-2;
        $table_data["rows"] = $data;
        $this->ajaxReturn($table_data);
    }
}