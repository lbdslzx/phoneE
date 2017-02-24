<?php
/**
 * @example      消息推送
 * @file         Push.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2016/1/23 0023
 * @time         12:40
 */
class Push{
    /**
     * 通过通知模板推送消息
     * @param int $userIds
     * @param $tempId
     * @param $clientType
     * @return bool
     */
    public static function messagePushByTemp($userIds,$tempId,$clientType = 1){
        if($userIds == 0){
            $actionType = "BROADCASTALL";
            $request = [
                'noticeBody'    => [
                    'noticeTempId'  => $tempId,
                    'client_type'   => $clientType
                ]
            ];
        }else{
            $actionType = "SENDTOALIAS";
            $request = [
                'userIds' => ['userId'=>$userIds],
                'noticeBody'    => [
                    'noticeTempId'  => $tempId,
                ]
            ];
        }
        return self::_push($actionType,$request);
    }

    /**
     * 消息推送
     * @param $actionType
     * @param $request
     * @return bool
     */
    private static function _push($actionType,$request){
        $xml = ArrayToXML::createXML('req',$request);
        $xml = $xml->saveXML();
        $xml = strtr($xml,["\t"=>"","\n"=>"","\r"=>""]);
        $requestEncrypted = Common::aes_encrypt($xml);
        $request = [
            'op_type'           =>  9201,
            'antionType'        =>  $actionType,
            'requestEncrypted'  =>  $requestEncrypted,
        ];
        $json = ['json'=>json_encode($request)];
        $url = C("push_cfg.url");
        $result = Common::post($url,$json);
        $result = json_decode($result,true);
        if(isset($result['code']) && $result['code'] == 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 消息推送
     * @param $noticeType
     * @param $content
     * @param $title
     * @param $desc
     * @param $clientType
     * @return bool
     */
    public static function messagePush($noticeType,$content,$title,$desc,$clientType = 3){
        $payload = [
            'notice_type'   => $noticeType,
            'content'       => $content,
        ];
        $payload = json_encode($payload);
        $request = [
            'noticeBody' => [
                'title'         => $title,
                'desc'          => $desc,
                'payload'       => $payload,
                'client_type'   => $clientType,
            ]
        ];
        return self::_push("ACTIVITY",$request);
    }

    static function sendCommonNotice($userId,$title,$desc,$details,$lablename,$lablecolor){
        $request = array(
            'userIds'=>array(
                'userId'=>array($userId)
            ),
            'noticeBody' => array(
                'title' => $title,
                'desc' => $desc,
                'details' => $details,
                'lablename'=>$lablename,
                'lablecolor'=>$lablecolor
            )
        );
        return self::_push("COMMON",$request);
    }

    static function sendOnlyOneNotice($userId,$title,$desc,$notice_type,$content){
        $payload = array('notice_type'=>$notice_type,'content'=>$content);

        $request = array(
            'userIds'=>array(
                'userId'=>array($userId)
            ),
            'noticeBody' => array(
                'title' => $title,
                'desc' => $desc,
                'payload'=>$payload
            )
        );

        return self::_push("ONLY_ONE_NOTICE",$request);
    }

    /**
     * 短信推送
     * @param $mobiles
     * @param $content
     * @return bool
     */
    public static function SMSPush($mobiles,$content){
        if(!is_array($mobiles) OR strlen($content) <= 0) return false;
        $request = [
            "userPhones"    =>  $mobiles,
            "noticeBody"    =>  ["smsContent"=>$content],
        ];
        return self::_push("SENDSMS",$request);
    }
}