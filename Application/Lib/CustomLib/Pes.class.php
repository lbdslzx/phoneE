<?php
/**
 * 与pes服务器通信类
 * @author yangtao 2012-09-05
 */
class PES extends CommonAction{
//    var $host;
//    var $port;
    var $pes_info;
    var $log_data;
    
    function __construct() {
//        $this->host = $GLOBALS["p_pes"]["host"];
//        $this->port = $GLOBALS["p_pes"]["port"];
        $this->pes_info = new PesInfoMemcache();
    }
    /**
     * 功能说明：从PES服务器获取帖子ID
     * @param $user_id  用户ID
     * @param $author_id 原帖作者ID
     * @return string 帖子ID
     */
    function getThreadId($user_id,$author_id) {        
        $pes_info = $this->pes_info->getPesInfo($author_id);
        $return->result = 0;
        $return->thread_id = "";
        if(count($pes_info) == 0){
            $return->result = -5;
            return $return;
        }
        if(!function_exists("php_blog_thread_id_rq")){
            $return->result = -5;
            return $return;
        }
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        $php_more_pack_rq = php_blog_thread_id_rq($user_id,$author_id);
        $data = $php_more_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_more_pack_rq["dataLen"]);
        $send_count = 0;//重发计数
        $result = false;
        while(!$result){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,3);
            if($result["data"]["result"] == "0"){
                $return->result = 0;
                $return->thread_id = $result["data"]["thread_id"];
                if(!$return->thread_id){
                    $return->result = -5;
                    Log::logWrite("php_blog_thread_id_rq:user_id:".$user_id.",author_id:".$author_id.",result:".var_export($result,TRUE),'pes_err');
                }
                return $return;
            }else{
                $return->result = -5;
                $result = FALSE;
                Log::logWrite("php_blog_thread_id_rq:user_id:".$user_id.",author_id:".$author_id.",result:".var_export($result,TRUE),'pes_err');
            }
            $send_count++;
        }
        if (!$result || ($result["data"] && $result["data"]["result"]!=0))
        {
            Log::logWrite("php_blog_thread_id_rq:user_id:".$user_id.",author_id:".$author_id.",result:".var_export($result,TRUE),'pes_err');
        }

        return $return;
    }

    //检测游客权限
    public function checkVisitors($user_id,$op_type)
    {
        $pesInfo=$this->pes_info->memcache->get($this->pes_info->key);
        foreach($pesInfo as $eachPesInfo)
        {
            if($eachPesInfo['server_type']==2)
            {
                $visitorsInfo[]=$eachPesInfo;
                //判断user_id是否在游客内 
                $flag=($eachPesInfo['user_id_begin']<=$user_id&&$eachPesInfo['user_id_end']>=$user_id)?1:0;
            }
        }
        if($flag)
            return in_array($op_type, $GLOBALS['visitorsApi']);
        else
            return FALSE;
    }

    /**
     * 功能说明：通知PES更新包
     * @param   $user_id        原帖用户ID
     * @param   $thread_id      原帖ID
     * @param   $notice_type    通知类型
     * @param   $list           通知用户列表
     * @return boolean
     */  
    function noticeThreadPes($user_id, $user_name,$src_user_id,$thread_id,$src_thread_id,$notice_type,$thread_type,$act=""){
        $this->log_data .= Log::formatData("PES通知用户更新开始时间:".date("Y-m-d H:i:s").".".substr(microtime(), 2,6));
        if(!function_exists("php_blog_thread_update_id")){
            return  false;
        }
        $pes_info = $this->pes_info->getPesInfo($src_user_id);
        if(count($pes_info) == 0)return false;
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        $php_more_pack_rq = php_blog_thread_update_id ($user_id,$user_name,$src_user_id,$thread_id,$src_thread_id,$notice_type,$thread_type,$act);
        $data = $php_more_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_more_pack_rq["dataLen"]);
        $result = false;
        $send_count = 0;
        while(!$result){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,0);
            if($result){
                break;
            }
            Log::logWrite("php_blog_thread_update_id第".$send_count."失败:".var_export($result,TRUE).";user_id:".$user_id.";user_name".$user_name.";src_user_id".$src_user_id.";thread_id:".$thread_id.";notice_type".$notice_type.";act_id".$act,'pes_err');
            $send_count++;
        }

        if (!$result || ($result["data"] && $result["data"]["result"]!=0))
        {
            Log::logWrite("php_blog_thread_update_id:user_id:".$user_id.",user_name:".$user_name
                .",src_user_id:".$src_user_id.",thread_id:".$thread_id
                .",src_thread_id:".$src_thread_id.",notice_type:".$notice_type
                .",thread_type:".$thread_type.",result:".var_export($result,TRUE),'pes_err');
        }

        return $result;
    }
    
    //如果$waitAckSec=0，则返回成功发送的字节�?
    //如果$waitAckSec大于0，则返回发送后接收到得内容
    //任何情况下，失败都返回FALSE
    function sendUdp($host, $port, $buff,$waitAckSec=0){
        $result = FALSE;
        $socket = ($result = @socket_create(AF_INET,SOCK_DGRAM,SOL_UDP));
        if($socket){
//            getprotobyname($name);
            $result = @socket_sendto($socket,$buff,strlen($buff),0,$host,$port);
            if($waitAckSec>0){
                $result = FALSE;
                $read = array($socket);
                $write = NULL;
                $except = NULL;
                if(@socket_select($read,$write,$except,$waitAckSec)>0){
                    $fromHost = "";
                    $fromPort = 0;
                    @socket_recvfrom($socket,$result,4096,0,$fromHost,$fromPort);	
                    $result = php_unpack($result);
                    if($result["needAck"] == 1){
                        $this->sendUdp($host, $port, $result["ackdata"]);
                        if(isset ($result['data']['list']) && isset ($result['data']['totalCount'])){
                            $list = $result['data']['list'];
                            $count = $result['data']['totalCount'];
                            while($count>  count($list)){
                                @socket_recvfrom($socket,$result_temp,4096,0,$fromHost,$fromPort);
                                $result_temp = php_unpack($result_temp);
                                $this->sendUdp($host, $port, $result_temp["ackdata"]);
                                $list = array_merge($list,$result_temp['data']['list']);
                            }	
                            $result['data']['list'] = $list;
                        }
                    }else{
                        @socket_recvfrom($socket,$result,4096,0,$fromHost,$fromPort);
                        $result = php_unpack($result);
                        if($result["needAck"] == 1){
                            $this->sendUdp($host, $port, $result["ackdata"]);
                            if(isset ($result['data']['list']) && isset ($result['data']['totalCount'])){
                                $list = $result['data']['list'];
                                $count = $result['data']['totalCount'];
                                while($count>  count($list)){
                                    @socket_recvfrom($socket,$result_temp,4096,0,$fromHost,$fromPort);
                                    $result_temp = php_unpack($result_temp);
                                    $this->sendUdp($host, $port, $result_temp["ackdata"]);
                                    $list = array_merge($list,$result_temp['data']['list']);
                                }	
                                $result['data']['list'] = $list;
                            }
                        }
                    }
                }else{
                    $result = SEND_UDP_ERROR;
//                    echo "socket_select() failed.reason:" .socket_strerror(socket_last_error())."<br>";
                }
            }
            @socket_close($socket);
        }
        return $result;
    }
    
    function getPesIp($str){
        return long2ip($this->ntohl($str));
    }
    function getPesPort($str){
        return $this->ntohs($str);
    }


    function ntohl($str){
        $arr = unpack("I", pack("N",$str));
        return $arr[1];
    }
    function ntohs($str){
        $arr = unpack("v", pack("n",$str));
        return $arr[1];
    }
    /**
     * 通知积分托换请求
     * @param $user_id     被关注人
     * @param $trade_id     订单号
     * @return $frozen_grade       冻结积分
     */
    function noticeGradePes($trade_id,$user_id,$frozen_grade)
    {
        $pes_info = $this->pes_info->getPesInfo($user_id);
        if(count($pes_info) == 0)return false;
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        $php_pack_rq = php_user_grade_frozen_rq($trade_id,intval($user_id), intval($frozen_grade));
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_pack_rq["dataLen"]);
        $result = false;
        $send_count = 0;
        while(!$result){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,1);

            if($result){
                break;
            }
            Log::logWrite("php_user_grade_frozen_rq".$send_count."失败:".var_export($result,TRUE).";trade_id".$trade_id.";user_id".$user_id.";frozen_grade".$frozen_grade,'pes_err');
            $send_count++;
        }
        if (!$result || ($result["data"] && $result["data"]["result"]!=0))
        {
            Log::logWrite("php_user_grade_frozen_rq:user_id:".$user_id.",trade_id:".$trade_id.",frozen_grade:".$frozen_grade.",result:".var_export($result,TRUE),'pes_err');
        }

        return $result;
    }
    /**
     * 通知积分托换请求
     * @param $user_id     被关注人
     * @param $trade_id     订单号
     * @return $exchg_grade       兑换积分
     * @return $exchg_result
           0，兑换成功，将冻结的积分转换为已兑换积分；
           1，兑换失败，将冻结的积分转换为当前积分；
     */
    function noticeGradeExchange($trade_id,$user_id,$exchg_grade,$exchg_result)
    {
        Log::logWrite("积分兑换trade_id：".$trade_id, $user_id, LOG_POST);
        Log::logWrite("积分兑换exchg_grade：".$exchg_grade, $user_id, LOG_POST);
        Log::logWrite("积分兑换exchg_result：".$exchg_result, $user_id, LOG_POST);
        $pes_info = $this->pes_info->getPesInfo($user_id);
        if(count($pes_info) == 0)return false;
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        $php_pack_rq = php_user_grade_exchg_rq($trade_id,intval($user_id), $exchg_grade,$exchg_result);
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_pack_rq["dataLen"]);
        $result = false;
        $send_count = 0;
        while(!$result){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,1);

            if($result){
                break;
            }
            Log::logWrite("php_user_grade_exchg_rq".$send_count."失败:".var_export($result,TRUE),'pes_err');
            $send_count++;
        }
        if (!$result || ($result["data"] && $result["data"]["result"]!=0))
        {
            Log::logWrite("php_user_grade_exchg_rq:user_id:".$user_id.",trade_id:".$trade_id
                .",exchg_grade:".$exchg_grade.",exchg_result:".$exchg_result
                .",result:".var_export($result,TRUE),'pes_err');
        }

        return $result;
    }    
    /**
     * 通知被关注人并增加魅力值
     * @param $user_id     被关注人
     * @param $fans_id     粉丝
     * @return mixed       0 成功
     */
    function noticeFansPes($user_id,$fans_id,$act_type)
    {
        $pes_info = $this->pes_info->getPesInfo($fans_id);
        if(count($pes_info) == 0)return false;
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        if ($act_type == FOLLOW_ACTION_ADD)
        {
            $php_pack_rq = php_add_fans_rq(intval($user_id), intval($fans_id));
        } else
        {
            $php_pack_rq = php_del_fans_rq(intval($user_id), intval($fans_id));
        }
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_pack_rq["dataLen"]);
        $result = false;
        $send_count = 0;
        while(!$result){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,1);

            if($result){
                break;
            }
            Log::logWrite("粉丝添加删除；act_type:".$act_type."失败:".var_export($result,TRUE)."user_id:".$user_id."fans_id:".$fans_id,'pes_err');
            $send_count++;

        }

        if (!$result || ($result["data"] && $result["data"]["result"]!=0))
        {
            if ($act_type == FOLLOW_ACTION_ADD)
            {
                Log::logWrite("php_add_fans_rq:user_id:".$user_id.",fans_id:".$fans_id.",act_type:".$act_type
                    .",result:".var_export($result,TRUE),'pes_err');
            } else
            {
                Log::logWrite("php_del_fans_rq:user_id:".$user_id.",fans_id:".$fans_id.",act_type:".$act_type
                    .",result:".var_export($result,TRUE),'pes_err');
            }
        }
        return $result;
    }

    /**
     * 送礼时扣除金币等操作
     * @param $order_id
     * @param $user_id
     * @param $recevier_id
     * @param $gift_id
     * @param $gift_name
     * @param $gift_price
     * @param $charm_value
     * @param $wealth_value
     * @param $grade_value
     * @return mixed
     */
    function sendGiftPes($order_id,$user_id,$recevier_id,$gift_id,$gift_name,$gift_price,$charm_value,$wealth_value,$grade_value,$spuid,$flag=0)
    {

        $pes_info = $this->pes_info->getPesInfo($user_id);
        if(count($pes_info) == 0)return false;
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        $php_pack_rq = php_send_gift_rq(
            $order_id,
            intval($user_id),
            intval($recevier_id),
            $gift_id,
            $gift_name,
            $gift_price,
            $charm_value,
            $wealth_value,
            $grade_value,
            $spuid,
            $flag
        );
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_pack_rq["dataLen"]);
        $result = false;
        $send_count = 0;
        while(!$result){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,1);

            if($result){
                break;
            }
            Log::logWrite("php_send_gift_rq第".$send_count."失败:".var_export($result,TRUE).";order_id".$order_id,'pes_err');
            $send_count++;
        }

        if (!$result || ($result["data"] && $result["data"]["result"]!=0))
        {
            Log::logWrite("php_send_gift_rq:user_id:".$user_id.",order_id:".$order_id.",recevier_id:".$recevier_id
                .",gift_id:".$gift_id.",gift_name：".$gift_name.",gift_price".$gift_price
                .",charm_value:".charm_value.",wealth_value:".$wealth_value
                .",grade_value:".$grade_value.",result:".var_export($result,TRUE),'pes_err');
        }

        return $result;
    }
    /**
     * 将用户强制踢下线
     * @param $user_id
     * @return mixed
     */
    function kickOff($user_id)
    {
    	$pes_info = $this->pes_info->getPesInfo($user_id);
    	if(count($pes_info) == 0)return false;
    	$host = $pes_info["pes_ip"];
    	$port = $pes_info["pes_port"];
    	$php_pack_rq = php_user_kickoff_rq(	
    			$user_id  	
    			);
    	$data = $php_pack_rq["data"]["data"];
    	$buff = substr($data, 0,$php_pack_rq["dataLen"]);
    	$result = false;
    	$send_count = 0;
    	while(!$result){
    		if($send_count == 3)break;//重发3次后跳出
    		$result = $this->sendUdp($host, $port, $buff,1);
    
    		if($result){
    			break;
    		}
    		Log::logWrite("php_user_kickoff_rq第".$send_count."失败:".var_export($result,TRUE)."user_id".$user_id,'pes_err');
    		$send_count++;
    	}
    
    	if (!$result || ($result["data"] && $result["data"]["result"]!=0))
    	{
    		Log::logWrite("php_user_kickoff_rq:user_id:".$user_id.",result:".var_export($result,TRUE),'pes_err');
    	}
    
    	return $result;
    }
    
    /**
     * 用户随机摇取金币请求,生成token，等用户领取时给用户添加金币
     * @param $user_id
     * @param $coin_num
     * @return mixed
     */
    function randomMeetPes($user_id,$coin_num)
    {
        $pes_info = $this->pes_info->getPesInfo($user_id);
        if(count($pes_info) == 0)return false;
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        $php_pack_rq = php_random_shake_coin_rq(intval($user_id), intval($coin_num));
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_pack_rq["dataLen"]);
        $result = false;
        $send_count = 0;
        while(!$result){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,32);

            if($result){
                break;
            }
            $send_count++;

        }
        if (!$result || ($result["data"] && $result["data"]["result"]!=0))
        {
            Log::logWrite("php_random_shake_coin_rq:user_id:".$user_id.",coin_num:".$coin_num
                .",result:".var_export($result,TRUE),'pes_err');
        }

        return $result;

    }

    /**
     * 验证用户session_id,如果与服务器返回的session_id不一致则返回false
     * @param $user_id
     * @param $coin_num
     * @return mixed
     */
    function checkSessionId($user_id,$opType,$sid)
    {
        if ($sid=="ipengsid") return true;

        if (in_array($opType,$GLOBALS['unvalidApiList']))
        {
            return true;
        }
        if (in_array($user_id,$GLOBALS['testUserList']))
        {
            return true;
        }
        $pes_info = $this->pes_info->getPesInfo($user_id);
        if(count($pes_info) == 0)return false;
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        $php_pack_rq = php_user_login_session_rq(intval($user_id));
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_pack_rq["dataLen"]);
        $result = false;
        $send_count = 0;
        while(!$result){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,1);

            if($result){
                break;
            }

            $send_count++;
        }
        Log::logWrite($result["data"]["session_id"],"session_check");

        if (!$result || ($result["data"] && $result["data"]["result"]!=0))
        {
            Log::logWrite("php_user_login_session_rq:user_id:".$user_id.",opType:".$opType.",sid:".$sid
                .",result:".var_export($result,TRUE),'pes_err');
        }

        if ($result["data"] && $result["data"]["result"]==0 && $result["data"]["session_id"]==$sid)
            $result = true;
        else
            $result = false;

        return $result;

    }

    /*
     * 地区信息
     */
    function areaInfo($user_id,$location)
    {
        $pes_info = $this->pes_info->getPesInfo($user_id);
        if(count($pes_info) == 0)return false;
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        if($location=="")
            $location="";
        else
            $location=json_encode($location);
        $php_pack_rq = php_user_location_update_rq(intval($user_id),$location);
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_pack_rq["dataLen"]);
        $result = false;
        $send_count = 0;
        while(!$result){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,1);

            if($result){
                break;
            }

            $send_count++;
        }
        Log::logWrite("php_user_location_update_rq:user_id:".$user_id.",location:".var_export($location,TRUE)
            .",result:".var_export($result,TRUE),'pes_err');
        if ($result["data"] && $result["data"]["result"]==0)
            $result = true;
        else
            $result = false;

        return $result;

    }
    /*
     * 用户池通知PES
     */
    function userPool($user_id,$opType)
    {
        $pes_info = $this->pes_info->getPesInfo($user_id);
        if(count($pes_info) == 0)return false;
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        $php_pack_rq = php_user_pool_rq(intval($user_id),$opType);
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_pack_rq["dataLen"]);
        $result = false;
        $send_count = 0;
        while(!$result){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,1);

            if($result){
                break;
            }

            $send_count++;
        }
        Log::logWrite("php_user_pool_rq:user_id:".$user_id."；aciton:".$opType
            .",result:".var_export($result,TRUE),'pes_err');
        if ($result["data"] && $result["data"]["result"]==0)
            $result = true;
        else
            $result = false;

        return $result;

    }
    /** 苹果商店购买金币 */
    function buyCoinsFromIOS($user_id,$trans_id,$incr_val,$trans_dt)
    {
        $pes_info = $this->pes_info->getPesInfo($user_id);
        if(count($pes_info) == 0)return -1;
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        $php_pack_rq = php_user_add_iap_receipt_rq(intval($user_id),$trans_id,$incr_val,$trans_dt);
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_pack_rq["dataLen"]);
        $result = -1;
        $send_count = 0;
        while($result==-1){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,1);

            if($result){
                break;
            }
            $send_count++;

        }
        if (!$result || ($result["data"] && $result["data"]["result"]!=0))
        {
            Log::logWrite("php_user_add_iap_receipt_rq:user_id:".$user_id.",trans_id:".$trans_id.",incr_val:".$incr_val
                .",trans_dt:".$trans_dt.",result:".var_export($result,TRUE),'pes_err');
        }

        if ($result["data"] && $result["data"]["result"]==0)
            $result = 0;
        else if ($result["data"] && $result["data"]["result"]==1030019)
            $result = -101;    //-101 记录已存在
        else
            $result = -1;

        return $result;
    }

    /** 支付宝购买金币 */
    function buyCoinsFromAlipay($user_id,$trans_id,$out_trans_no,$incr_val,$trans_dt)
    {
        $pes_info = $this->pes_info->getPesInfo($user_id);
        if(count($pes_info) == 0)return -1;
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        $php_pack_rq = php_user_add_alipay_receipt_rq($user_id, $trans_id, $out_trans_no, $incr_val, $trans_dt);
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_pack_rq["dataLen"]);
        $result = -1;
        $send_count = 0;
        while($result==-1){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,1);

            if($result){
                break;
            }
            $send_count++;

        }

        if (!$result || ($result["data"] && $result["data"]["result"]!=0))
        {
            Log::logWrite("php_user_add_alipay_receipt_rq:user_id:".$user_id.",trans_id:".$trans_id
                .",incr_val:".$incr_val.",trans_dt:".$trans_dt.",out_trans_no:".$out_trans_no
                .",result:".var_export($result,TRUE),'pes_err');
        }

        if ($result["data"] && $result["data"]["result"]==0)
            $result = 0;
        else if ($result["data"] && $result["data"]["result"]==1030022)
            $result = -101;    //-101 记录已存在
        else
            $result = -1;

        return $result;
    }
    /** 运营商支付 */
    function buyCoinsFromMM($user_id,$trans_id,$out_trans_no,$incr_val,$trans_dt,$type)
    {
        $pes_info = $this->pes_info->getPesInfo($user_id);
        if(count($pes_info) == 0)return -1;
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        $php_pack_rq = php_user_mm_pay_rq($user_id, $trans_id, $out_trans_no, $incr_val, $trans_dt,$type);
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_pack_rq["dataLen"]);
        $result = -1;
        $send_count = 0;
        while($result==-1){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,1);

            if($result){
                break;
            }
            $send_count++;

        }

        Log::logWrite("php_user_mm_pay_rq:user_id:".$user_id.",trans_id:".$trans_id
                .",incr_val:".$incr_val.",trans_dt:".$trans_dt.",out_trans_no:".$out_trans_no
                .",result:".var_export($result,TRUE),'pes_date');
        
//        if (!$result || ($result["data"] && $result["data"]["result"]!=0))
//        {
//            Log::logWrite("php_user_mm_pay_rq:user_id:".$user_id.",trans_id:".$trans_id
//                .",incr_val:".$incr_val.",trans_dt:".$trans_dt.",out_trans_no:".$out_trans_no
//                .",result:".var_export($result,TRUE),'pes_err');
//        }

        if ($result["data"] && $result["data"]["result"]==0)
            $result = 0;
        else if ($result["data"] && $result["data"]["result"]==1030022)
            $result = -101;    //-101 记录已存在
        else
            $result = -1;

        return $result;
    }
    
    /**
     * 管理系统手工充值币值
     * @param type $user_id         用户ID
     * @param type $control_type    充值类型：1-赠送金币 2-购买金币 3-金豆 4-魅力值 5-财富值    
     * @param type $action          充值方式：1-增加 2-减少
     * @param type $incr_val        充值金额数
     * @param type $trade_id        单据号
     * @param type $trans_dt        当前时间戳
     */
    function chargeCoinsByManual($user_id,$control_type,$action,$incr_val,$trade_id,$trans_dt,$flag=0)
    {
        $pes_info = $this->pes_info->getPesInfo($user_id);

        if(count($pes_info) == 0){
            return -1;
        }

        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        if($flag==1)
            $user_id=0;
        $php_pack_rq = php_user_currency_control_rq($user_id, $control_type, $action, $incr_val, $trade_id, $trans_dt);
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_pack_rq["dataLen"]);
        $result = -1;
        $send_count = 0;

        while($result==-1){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,1);

            if($result){
                break;
            }
            $send_count++;

        }

        Log::logWrite("php_user_currency_control_rq:user_id:".$user_id."；pes ip地址：".$pes_info["pes_ip"]."；pes 端口地址：".$pes_info["pes_port"].",trade_id:".$trade_id
                .",incr_val:".$incr_val.",trans_dt:".$trans_dt.",control_type:".$control_type
                .",action:".$action.",result:".var_export($result,TRUE),'pes_err');

        if ($result["data"] && $result["data"]["result"]==0){
            $result = 0;
        }else{
            $result = -1;
        }

        return $result;
    }
    /**
     * 审核系统头像变更
     * @param int $user_id
     * @return int 0成功，其它失败
     */
    function updateUserAvatarState($user_id){
        $pes_info = $this->pes_info->getPesInfo($user_id);
        
        if(count($pes_info) == 0){
            return -1;
        }
            
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        $php_pack_rq = php_user_avatar_state_update_rq($user_id);
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0,$php_pack_rq["dataLen"]);
        $result = -1;
        $send_count = 0;
        
        while($result==-1){
            if($send_count == 3)break;//重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff,1);

            if($result){
                break;
            }
            $send_count++;

        }

        Log::logWrite("php_user_avatar_state_update_rq:user_id:".$user_id.",result:".var_export($result,TRUE),'pes_err');
        
        if ($result["data"] && $result["data"]["result"]==0){
            $result = 0;
        }else{
            $result = -1;
        }
        return $result;
    }
    
    /**
     * 用户验证信息变更
     * @param string $user_id       用户ID
     * @param string $verify_info   验证信息，JSON格式
     * @return int                  0成功，其它失败
     */
    function updateUserVerifyInfo($user_id,$verify_info){
        $pes_info = $this->pes_info->getPesInfo($user_id);

        if (count($pes_info) == 0) {
            return -1;
        }

        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
        $php_pack_rq = php_user_verify_info_update_rq($user_id, $verify_info);
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0, $php_pack_rq["dataLen"]);
        $result = -1;
        $send_count = 0;

        while ($result == -1) {
            if ($send_count == 3)
                break; //重发3次后跳出
            $result = $this->sendUdp($host, $port, $buff, 1);

            if ($result) {
                break;
            }
            $send_count++;
        }

        if (!$result || ($result["data"] && $result["data"]["result"] != 0)) {
            Log::logWrite("php_user_verify_info_update_rq:user_id:" . $user_id . ",verify_info:" . $verify_info
                    . ",result:" . var_export($result, TRUE), 'pes_err');
        }

        Log::logWrite("php_user_verify_info_update_rq:user_id:" . $user_id . ",verify_info:" . $verify_info
            . ",result:" . var_export($result, TRUE), 'pes_err');

        if ($result["data"] && $result["data"]["result"]==0){
            $result = 0;
        }else{
            $result = -1;
        }

        return $result;
    }
    
    
 
/**
     * web聊天系统发送短信接口
     * @param string $user_id      发送者uid
     * @param string $count   发送个数
     * @param string $receive_list   发送uid的字符串，多个用户中间用,分割：例如10023,10024,10025,
     * @param string $seqId   序列号=0
     * @param string $smsContentJson   短信内容的json {"un":"候军","c":"吧","mt":0,"st":101}
     * @return int                  0成功，其它失败
     */
    function sendWebSms($user_id,$count,$receive_list,$seqId,$smsContentJson){
        $pes_info = $this->pes_info->getPesInfo($user_id);
        
        $this->logWrite("pesInfo:".print_r($pes_info,true), "send_sms");
		
        if (count($pes_info) == 0) {
            return -2;
        }
		
        $host = $pes_info["pes_ip"];
        $port = $pes_info["pes_port"];
       $this->logWrite("pes调用php_send_webmsg_rq:参数".$user_id.",".$count.",".$receive_list.",".$seqId.",".$smsContentJson.",".$user_id,"send_sms");
        $php_pack_rq = php_send_webmsg_rq($user_id,$count, $receive_list,$seqId,$smsContentJson);
        $data = $php_pack_rq["data"]["data"];
        $buff = substr($data, 0, $php_pack_rq["dataLen"]);
        $result = -1;
        $send_count = 0;

        while ($result == -1) {
            if ($send_count == 3)
                break; //重发3次后跳出
            $this->logWrite("sendUdp:host:".$host."port:".$port."buff:".print_r($buff,true), "send_sms");
            $result = $this->sendUdp($host, $port, $buff, 1);

            if ($result) {
                break;
            }
            $send_count++;
        }
     
        if ($result["data"] && $result["data"]["result"]==0){
            $result = 0;
        }else{
            $result = -1;
        }

        return $result;
    }

}


/**
 * 获取PES服务器连接信息memcache操作类
 * @author yangtao 2012-09-11
 */
class PesInfoMemcache extends CommonAction{
    var $memcache;
    var $key;
    var $list;
    function __construct() {
        if(!class_exists("P_Memcache")){
            import("@.CustomLib.P_Memcache");
        }
        $this->key = "pes_info";
        $this->memcache = new P_Memcache();
        $this->memcache->config = C("p_memcache_global_list");
        $this->memcache->connect();
        $this->init();
    }
    function __destruct() {
        $this->memcache->close();
    }


    function init($isReset = false){
        $this->list = $this->memcache->get($this->key)?$this->memcache->get($this->key):array();
        if(count($this->list)==0 || $isReset){
            $db = M("t_pes_info",null,"db_sys");
            $sql = "select pes_ip,pes_port,user_id_begin,user_id_end,user_id_seg_idx,server_type from t_pes_info;";
            $this->list = $db->query($sql);
            $this->memcache->set($this->key, $this->list);
        }
    }
    
    function getPesInfo($user_id){
        for($i=0;$i<count($this->list);$i++){
            $row = $this->list[$i];
            if($user_id >= $row['user_id_begin'] && $user_id <= $row['user_id_end']){
//                $row["pes_ip"] = long2ip($this->ntohl(floatval($row["pes_ip"])));
//                $row["pes_port"] = $this->ntohs(floatval($row["pes_port"]));
                //ipeng的ip和端口不再另外转换
//                 $row["pes_ip"] = $row["pes_ip"];
//                 $row["pes_port"] = $row["pes_port"];
                return $row;
            }
        }
        return array();
    }
    
    function ntohl($str){
        $arr = unpack("I", pack("N",$str));
        return $arr[1];
    }
    function ntohs($str){
        $arr = unpack("v", pack("n",$str));
        return $arr[1];
    }

}

function var_dump_ret($mixed = null) {
    ob_start();
    var_dump($mixed);
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}
?>
