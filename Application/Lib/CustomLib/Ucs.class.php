<?php
/**
 * 与ucs服务器通信类
 */
define("SEND_UDP_ERROR", -1);//UDP发送失败
define("UCS_PACKAGE_TYPE_PBSEXT", 4);//pbs扩展
define("UCS_PACKAGE_TYPE_PHPEXT", 5);//php扩展

define("NO_SUCH_PHP_FUNCTION", -5); //php接口不存在
define("NO_UCS_INFO", -6);  //没有相关UCS信息

class Ucs{
    public $ucs_info;
    function __construct() {
        $this->ucs_info = C('ucs_cfg');
    }
    
    /**
     * 执行UCS相关的任务
     * @param string $ext_name 扩展名
     * @param array $params 多个参数
     * @return array <multitype:string , string>
     */
    public static function execTask($ext_name,$params){
    	$ucs = new ucs();
    	$args = func_get_args();
    	$ret = array();
    	if(!function_exists($ext_name)){
    		$ret["code"] = NO_SUCH_PHP_FUNCTION;
    		return $ret;
    	}
    	if(empty($ucs->ucs_info)){
    		$ret["code"] = NO_UCS_INFO;
    		return $ret;
    	}
    	$resp = $ucs->_execTask($ext_name,$params);
    	$ret = $ucs->_sendUdp($ucs->ucs_info, $resp, $ret);
    	return $ret;
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



    private function _execTask($ext,$params){
        $args = func_get_args();
        return call_user_func_array($ext, $params);
    }

    private function _sendUdp($ucs_info, $taskResp, $ret){

        $host = $ucs_info["host"];
        $port = $ucs_info["port"];
        $data = $taskResp['data']['data'];
        $buffer = substr($data, 0, $taskResp['dataLen']);
        $times = 0;
        $flag = false;
        while(!$flag){
            if($times == 3) break;
            $result = $this->sendUdp($host, $port, $buffer, 3);
            $ret["resp"] = $result['data'];
            if($result['data']['result'] == '0' || in_array($result['data']['result'],array(1030043))){
                $ret["code"] = '0';
                return $ret;
            }else{
                $ret["code"] = SEND_UDP_ERROR;
                $flag = false;
            }
            $times ++;
        }
        return $ret;
    }

    /**
     * 获取用户生日
     * @param int $user_id
     * @return string
     */
    public static function getUserBirthday($user_id){
        $user_id = intval($user_id);
        $data = self::execTask("php_user_property_rq",['user_id'=>$user_id,'user_type'=>1]);
        $birthday = $data["resp"]["birthday"];
        $birthday = substr($birthday, 0,4)."-".substr($birthday, 4,2)."-".substr($birthday, -2);
        return $birthday;
    }

}

