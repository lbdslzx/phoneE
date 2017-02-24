<?php

/**
 * Created by PhpStorm.
 * User: daxin.yang@longmaster.com.cn<daxin.yang@longmaster.com.cn>
 * Date: 2016/2/17
 * Time: 19:42
 * 功能说明：会员详细信息查询
 */
class vipInfoQueryAction extends CommonAction
{

    function __construct()
    {
        parent::__construct();
        import("@.CustomLib.Ucs");
    }

    function queryVipInfo()
    {
        $userTel = $_POST['user_tel'];
        if (empty($userTel)) {
            $this->ajaxReturn(-1);//手机号不存在
            return;
        }

        $userId = $this->getUserIdByTel($userTel);
        if (empty($userId)) {
            $this->ajaxReturn(-1);//手机号不存在
            return;
        }

        //获取vip信息
        $content = [
            'userID' => intval($userId)
        ];
        $result = UCS::execTask('php_user_query_vip_info_rq', $content);

        $vipInfo = array(
            'ivr_vip' => 0,
            'gjk_vip' => 0,
        );

        $resp = $result['resp'];
        if ($resp['result'] == 0) {
            if ($resp['vipType'] == 0) {//贵健康VIP
                $vipInfo['gjk_vip'] = 1;
            } elseif ($resp['vipType'] == 1) {
                $vipInfo['ivr_vip'] = 1;
            }
        }

        //获取问诊卡信息
        $couponsCount = 0;
        $couponsBalance = 0;
        $couponsInfo = $this->getUserCouponsInfo($userId);
        if (!empty($couponsInfo)) {
            $couponsCount = $couponsInfo[0]['count'];
            $couponsBalance = $couponsInfo[0]['free_coin'];
        }

        //获取健康币余额
        $coinBalance = $this->getUserCoinBalance($userId);

        //vip购买日志
        $buyVipLog = $this->getBuyVipLog($userId);

        $outData = array(
            'tel' => $userTel,
            'vipInfo' => $vipInfo,
            'couponsCount' => $couponsCount,
            'couponsBalance' => $couponsBalance,
            'coinBalance' => $coinBalance,
            'buyVipLog' => $buyVipLog
        );

        $this->ajaxReturn($outData);
    }

    /**
     * 获取手机号
     * @param $tel
     * @return null
     */
    function getUserIdByTel($tel)
    {
        $db = M("t_user_phone", null, DB_QUERY);
        if (!strstr($tel, '+86')) {
            $tel = '+86' . $tel;
        }
        $sql = "SELECT user_id FROM t_user_phone WHERE phone_num='$tel';";
        $data = $db->query($sql);
        $user_id = null;
        if (!empty($data)) {
            $user_id = $data[0]['user_id'];
        }
        return $user_id;
    }

    /**
     * 获取问诊卡信息
     * @param $userId
     * @return array|mixed
     */
    function getUserCouponsInfo($userId)
    {
        $db = M("t_user_coupons", null, DB_UC);
        $sql = "SELECT IFNULL(ABS(SUM(free_coin)),0) AS 'free_coin',COUNT(0) AS 'count' FROM t_user_coupons WHERE user_id='{$userId}' AND flag=0 AND end_dt>=NOW();";
        return $db->query($sql);
    }

    /**
     * 获取健康币余额
     * @param $userId
     * @return int
     */
    function getUserCoinBalance($userId)
    {
        $coinBalance = 0;
        $db = M("t_user_coupons", null, DB_UC);
        $sql = "SELECT coin_value FROM t_user_coin WHERE user_id='{$userId}';";
        $data = $db->query($sql);
        if (!empty($data)) {
            $coinBalance = $data[0]['coin_value'];
        }
        return $coinBalance;
    }

    function getBuyVipLog($userId)
    {
        $db = M("t_log_user_vip_monthly", null, DB_LOG_UC);
        $data = $db->field('user_id', 'change_days', 'vip_from', 'log_dt')
            ->where("user_id='{$userId}'")
            ->order('log_dt desc')
            ->select();
        return $data;
    }

}