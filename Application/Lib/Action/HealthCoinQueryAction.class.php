<?php
/**
 * Created by PhpStorm.
 * User: daxin.yang@longmaster.com.cn<daxin.yang@longmaster.com.cn>
 * Date: 2016/2/17
 * Time: 19:42
 * 功能说明：健康币记录查询
 */
class healthCoinQueryAction extends CommonAction{

    function __construct(){
        parent::__construct();
    }

    public function index(){
        $table_title = array(
            "trade_id"		     =>"流水号",
            "user_tel"		 =>"用户电话",
            "reason_desc"		 =>"操作类型",
            "upd_dt"       =>"操作时间",
            "change_value"   =>"操作健康币数量",
            "coin_value"       =>"健康币余额"
        );
        $this->assign("table_title",$table_title);
        $this->display();
    }

    function getList(){
        parent::getList();
        $db = M("t_user_coin_detail as a",null,DB_UC);

        $where = 'a.is_freeze=0 AND (a.change_value<>0 OR a.free_change_value<>0) ';
        if(!empty($this->bt)){
            $where .= " AND DATE_FORMAT(a.upd_dt,'%Y-%m-%d')>='{$this->bt}'";
        }

        if(!empty($this->et)){
            $where .= " AND DATE_FORMAT(a.upd_dt,'%Y-%m-%d')<='{$this->et}'";
        }

        $fields = array(
             "a.trade_id",
             "a.user_id",
             "CASE reason WHEN 101 THEN '购买健康币' WHEN 201 THEN '视频问诊(健康币)' WHEN 202 THEN '电话问诊(健康币)' WHEN 301 THEN '视频问诊(优惠券)' WHEN 302 THEN '电话问诊(优惠券)' END AS 'reason_desc'",
             "CASE reason WHEN 301 THEN abs(a.free_change_value) WHEN 302 THEN abs(a.free_change_value) ELSE abs(a.change_value) END AS 'change_value'",
             "a.coin_value",
             "DATE_FORMAT(a.upd_dt,'%Y-%m-%d %H:%i:%s') AS 'upd_dt'"
        );

        if(!empty($this->user_tel)){
            $userId = $this->getUserIdByTel($this->user_tel);
            $where .= " AND a.user_id='{$userId}'";
        }

        //总条数
        $totalRecords = $db->where($where)->count();

        //列表页内容
        $data = $db->field($fields)
            ->join("LEFT JOIN t_user_coin b on a.user_id=b.user_id")
            ->where($where)
            ->order("a.upd_dt desc")
            ->page($this->page,$this->rows)
            ->select();

        $userIds = array();
        foreach($data as $key=>$value){
            array_push($userIds,$value['user_id']);
        }

        $userIdStr = implode(',',array_unique($userIds));

        $userTels = $this->getUserTel($userIdStr);
        $list = array();
        foreach($data as $value1){
            foreach($userTels as $value2){
                if($value1['user_id'] == $value2['user_id']){
                    $value1['user_tel'] = $value2['user_tel'];
                    array_push($list,$value1);
                    break;
                }
            }
        }

        $table_data["total"] = $totalRecords;
        $table_data["rows"] = $list;
        $this->ajaxReturn($table_data);
    }

    /**
     * 根据id获取用户手机号
     * @param $userIds
     * @return null
     */
    function getUserTel($userIds){
        $db = M("t_user_phone",null,DB_QUERY);
        $sql = "SELECT user_id,REPLACE(phone_num,'+86','') AS 'user_tel' FROM t_user_phone WHERE user_id IN ({$userIds});";
        $data = $db->query($sql);
        return $data;
    }

    /**
     * 获取手机号
     * @param $tel
     * @return null
     */
    function getUserIdByTel($tel){
        $db = M("t_user_phone",null,DB_QUERY);
        if(!strstr($tel,'+86')){
            $tel = '+86'.$tel;
        }
        $sql = "SELECT user_id FROM t_user_phone WHERE phone_num='$tel';";
        $data = $db->query($sql);
        $user_id = null;
        if(!empty($data)){
            $user_id = $data[0]['user_id'];
        }
        return $user_id;
    }

}