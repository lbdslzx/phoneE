<?php

define("UPLOAD_SUCCESS", 0);//上传成功
define("UPLOAD_WRITE_ERROR", -1);//写入错误
define("UPLOAD_DIR_ERROR", -3);//文件夹错误
define("UPLOAD_NULL_STREAM", -4);//流为空
define("FILE_NOT_EXIST", -1);
/**
 *文件操作类
 *@author ysg
 */
class FileClass{

    public static function downloadFile($myFile,$offset = 0){
        if (file_exists($myFile)){
            $read=fopen($myFile,"r");
            if( $offset > 0 ){
                fseek($read, $offset);
            }
            $header_parms = array(
            	"Cache-Control"	=>	"public, must-revalidate",
            	"Pragma"	=>	"hack",
            	"Content-Type"	=>	"application/octet-stream",
            	"Content-Length"	=>	(string)(filesize($myFile)),
            	"Content-Disposition"	=>	'attachment; filename=' . basename($myFile),
            	"filename"	=>	basename($myFile),
            	"Content-Transfer-Encoding"	=>	"binary\n",
            ); 
            echo fread($read,filesize($myFile));
            fclose($read);
        }
    }

    /**
     * 接受上传流文件存储指定路径
     * @param mixed $stream 文件流
     * @param string $destination 文件路径+文件名
     */
    public static function streamUpload($stream,$destination){
    	$pathinfo = pathinfo($destination);
    	if($pathinfo["extension"] !="jpg" && $pathinfo["extension"] !="png"){
    		return "-100";
    	}
        if($stream){
            self::createDir(dirname($destination));
            $fullpath = $destination;
            if(@$fp = fopen($fullpath, 'w+')){
                if(@fwrite($fp, $stream) == strlen($stream)){
                    fclose($fp);
                    return UPLOAD_SUCCESS;
                }else{
                    fclose($fp);
                    return UPLOAD_WRITE_ERROR;
                }
            }else{
                return UPLOAD_DIR_ERROR;
            }
        }else{
            return UPLOAD_NULL_STREAM;
        }
    }

    /**
     * 创建文件夹
     * @param 文件夹路径名
     */
    private static function createDir($path,$mode=0777,$recursive=1){
        !is_dir($path) && mkdir($path, $mode, $recursive);
    }
    public static function backUp($source, $destination, $child)
    {
         //将$source 文件夹中的所有文件移动到另外一个文件夹中去
        // backUp("feiy", "feiy2", 1):拷贝feiy下的文件到 feiy2, 包括子目录
        // backUp("feiy", "feiy2", 0):拷贝feiy下的文件到 feiy2, 不包括子目录
		self::createDir($destination);
        if (!is_dir($source)||!is_dir($destination))
         {
             return FALSE;
         }
        $handle = dir($source);
        while ($entry = $handle->read())
        {
            if (($entry != ".") && ($entry != ".."))
            {
                if (is_dir($source . "/" . $entry))
                {
                    if ($child)
                    {
                        if(!self::backUp($source . "/" . $entry, $destination . "/" . $entry, $child))
							return FALSE;
                    }
                }else
                {
                    if(copy($source . "/" . $entry, $destination . "/" . $entry))
                       unlink($source . "/" . $entry);
                    else
                        return FALSE;
                }
                rmdir ($source . "/" . $entry);
            }
        }
        rmdir ($source);
        return TRUE;
    } 
    
    //对目标文件进行移动
    public function fileRemov($source,$destination)
    {
    	
    	if (!is_dir($destination))
    	{
    		if(!mkdir($destination, 0777,true)){
    			log::logWrite("创建目录失败:".$destination, $this->user_id);
    			return $this->result=-1;
    		}
    	}
    	if(!rename($source, $destination.$this->thread_id)){
    		log::logWrite("移动文件失败",$this->user_id);
    		log::logWrite("source:".$source."    \r\n destination:".$destination.$this->thread_id,$this->user_id);
    		$this->result = -1;
    	}
    	//         if (!FileClass::backUp($source, $destination . $this->thread_id, 1)) {
    	//             $this->result = -1;
    	//         }
    	//检测文件是否移动成功
    	if(!file_exists($destination."/".$this->thread_id."/".$GLOBALS["p_attach_type_path"][$this->attach->attach_type].$this->attach->attach_name))
    		return $this->result=-1;
    	else
    		return $this->result=0;
    }
    
    /**
     * 切割图片
     * @param string $destination   eg:  /data/1342.jpg
     * @param int $quality 图像质量
     * @param int $imgSize 图像大小
     *
     */
    public static  function cutImage($attach_name,$quality=70,$imgSize = 300)
    {
    	$thum_img = str_replace('.jpg', '_s.jpg', $attach_name);
    	$img = new ThumbHandler();
    	$img->setSrcImg($attach_name);
    	$img->setDstImg($thum_img);
    	$img->setImgDisplayQuality($quality);
    	$img->setImgCreateQuality($imgSize);
    	$img->setCutType(1);
    	$scal = ($img->src_h < $img->src_w) ? $img->src_h : $img->src_w;
    	$scal = ($scal < $imgSize) ? $scal : $imgSize;
    	if ($img->createImg($scal, $scal)) {
    		Log::log_trace("截取缩略图成功：".$attach_name );
    		return $thum_img;
    	} else {
    		Log::log_trace("截取缩略图失败：" . $attach_name);
    		return FALSE;
    	}
    }
    /**
     * mp4转flv格式
     * @param string $source 视频路径 eg:  /data/1342.mp4
     * @param int $quality 图像质量
     * @param int $imgSize 图像大小
     */
    public static  function mp4ToFlv($source)
    {
    	$destination = str_replace('.mp4', '.flv', $source); //flv路径
    	if(file_exists($destination))
    		return 0;
    	$shell_path = $GLOBALS['ser_cfg']["shell_path"]; //shell文件路径   
    	$shell = $shell_path . "mp4_1st_flv.sh $shell_path $source $destination";
    	$shell=str_replace("$",'\$',$shell);
    	Log::logWrite("生成flv的shell为：" . $shell, "flv", LOG_POST);
    	exec($shell, $result);
    	if ($result[0] == '1') {
    		Log::logWrite("生成flv文件成功：" . date("Y-m-d:H:i:s"), "flv", LOG_POST);
    		return TRUE;
    	} else {
    		Log::logWrite("生成flv文件失败：" . date("Y-m-d:H:i:s"), "flv", LOG_POST);
    		return FALSE;
    	}
    }
    /**
     * 为视频添加水印 
     * @param unknown $fileName
     * @param unknown $destination
     */
    public static function AddWatermark($fileName, $destination)
    {
    	//水印处理
    	$input = $destination . $this->newAttachName; //MP4路径
    	//            echo $input."......";
    	//            $shell_path = 'service/mp4_1st_image/';  //shell文件路径
    	$shell_path = $GLOBALS['sms_video_shell']; //shell文件路径
    	//            echo $shell_path;exit;
    	$output_name = $destination . $fileName . ".mp4"; //水印MP4路径
    	//            echo $output_name;exit;
    	if (file_exists($output_name))
    		unlink($output_name);
    	$output = str_replace('.mp4', '.jpg', $output_name); //jpg路径
    	$shell = $shell_path . "mp4_1st_image.sh $shell_path $input $output $output_name";
    	Log::logWrite("水印SHELL语句：" . $shell, $this->user_id, LOG_POST);
    	exec($shell, $result);
    	if ($result[0] == '1') {
    		Log::logWrite("水印成功：" . date("Y-m-d:H:i:s"), $this->user_id, LOG_POST);
    		return TRUE;
    	} else {
    		Log::logWrite("水印失败：" . date("Y-m-d:H:i:s"), $this->user_id, LOG_POST);
    		return FALSE;
    	}
    }
    
//     //对目标文件进行移动
//     public static function fileRemov($source,$destination)
//     {
    	
//     	if (!is_dir($source)&&!file_exists($source."/".$GLOBALS["p_attach_type_path"][$this->attach->attach_type].$this->attach->attach_name))
//     		return $this->result = -1;
//     	if (!is_dir($destination))
//     	{
//     		if(!mkdir($destination, 0777,true)){
//     			log::logWrite("创建目录失败:".$destination, $this->user_id);
//     			return $this->result=-1;
//     		}
//     	}
//     	if(!rename($source, $destination.$this->thread_id)){
//     		log::logWrite("移动文件失败",$this->user_id);
//     		log::logWrite("source:".$source."    \r\n destination:".$destination.$this->thread_id,$this->user_id);
//     		$this->result = -1;
//     	}
//     	        if (!FileClass::backUp($source, $destination . $this->thread_id, 1)) {
// 			    	//             $this->result = -1;
// 			    	//         }
// 			    	//检测文件是否移动成功
// 			    	if(!file_exists($destination."/".$this->thread_id."/".$GLOBALS["p_attach_type_path"][$this->attach->attach_type].$this->attach->attach_name))
// 			    		return $this->result=-1;
// 			    	else
// 			    		return $this->result=0;
// 			    }
    
//     }
    
    
    
    
    
    
    
}
?>
