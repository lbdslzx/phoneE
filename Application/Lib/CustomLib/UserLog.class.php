<?php

define("LOG_ERROR", "1"); //严重错误
define("LOG_TRACE", "2"); //日志跟踪，便于日后分析
define("LOG_RECORD", "3"); //输入日志记录
define("ENTER", "\r\n");
class UserLog{
	
	private static $user_id = null; 
	
	/**
	 *将单次会话日志写在同一个文件当中而不必每次传入user_id(文件名) 
	 *在同一次会话中第一次传入user_id有效
	 *@param string data
	 *@param string user_id
	 */
	public static function log_trace($data,$user_id = ""){
		if(empty(self::$user_id)){
			if(empty($user_id)){
				return "parms error";
			}else{
				self::$user_id = $user_id;
			}
		}
		self::logWrite($data, self::$user_id,self::$user_id."_trace",LOG_TRACE);
	}
	
    /**
     * 写日志
     * @param string $data
     * @param int $user_id
     * @param string $log_type 
     */
    public static function logWrite($data,$user_id,$log_type = "default",$Level = LOG_RECORD){
        $logCfg["is_log"] = C("is_log");
        $logCfg["log_level"] = 3;
        $logCfg["log_path"] =C("app_log_path");
        if($logCfg["is_log"] && $logCfg["log_level"] >= $Level ){
        	$path_pre =$logCfg["log_path"];
        	$path_pre = empty($path_pre)?"./log/":$path_pre;
                if($Level== LOG_ERROR){
                	$path = $path_pre. $user_id."/";
                } else if ($Level==LOG_TRACE){
                	$path = $path_pre.  date("Ym")."/".date("d")."-".date("H")."/".$user_id."/";
                }elseif ($Level==LOG_RECORD){
                	$path = $path_pre.  date("Ym")."/".date("d")."/";
                }
            $filename = $log_type.".txt";
            self::createDir($path);
            $path = $path.$filename;
            if (!is_writable($path)){
                @touch($path);
            }
            /* 每段日志之前插入分隔符*/
            $head = ENTER.ENTER.date("Y-m-d H:i:s").ENTER;
            $head .= '************************************************************'.ENTER;
            $write_data = $head.$data;
            $handle = @fopen($path, 'a');
            if ($handle) {
                fwrite($handle, $write_data);
                fclose($handle);
            }
        }
    }
    public static function trackTrace(){
        $str = '';
        $array = debug_backtrace();
        unset ($array[0]);
        foreach ($array as $row) {
            $str .= $row['file'].':'.$row['line'].'行，调用方法：'.$row['function'].'<br>';
        }
        return $str;
    }

    /**
     * 创建文件夹
     * @param 文件夹路径名
     */
    private static function createDir($path,$mode=0777,$recursive=1){
        !is_dir($path) && @mkdir($path, $mode, $recursive);
    }
    
    /**
     * 格式转换
     * @param object $data
     * @return string 
     */ 
    public static function formatData($data){
        $isLog = $GLOBALS['p_log_cfg']["is_log"];
        $return = '';
        if(!$isLog){
            return;
        }
        /* 数组和对象都格式化 */
        if(is_array($data) || is_object($data)){
            $return .= 'total_count:'.count($data)."\r\n";
            $return .= json_encode($data)."\r\n";
        }else{
            $return .= "$data\r\n";
        }
        return $return;
    }
   
}
?>
