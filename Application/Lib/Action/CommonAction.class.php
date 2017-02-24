<?php
/*
 * author ysg
 */
class CommonAction extends Action {
	public $xml ;
	public $page;
	public $rows;
	public $table_name ;
	public $db_name ;
	public $primary_key ;
	function __construct(){
		parent::__construct();
		$this->xml = RESOURCE_PUBLIC."xml/permission.xml";
		import("@.CustomLib.XMLToArray");

		$withoutLogin = $this->_withoutLogin();
		if(!$withoutLogin) {
			$this->init("html");//验证权限
			$this->checkLogin();
		}
 		import("@.CustomLib.UserLog");
 		import("@.CustomLib.HfsModel");
	}
	///无需登录页面
	protected function _withoutLogin(){
		$ipLevel = C("without_login");
		$clientIp = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
		$clientIp = $clientIp ? $clientIp : $_SERVER["REMOTE_ADDR"];
		foreach ($ipLevel as $key => $value){
			if($clientIp == $key){
				if($this->permissionCheck($value)){
					return true;
				}
			}
		}
		return false;
	}
	//检测是否登录超时
	public function checkLogin(){
		$cur_action = substr(__URL__,  strpos(__URL__, "index.php")+10);
		if($cur_action == "Login"){
			return true;
		}
		$nowtime = time();
		$onlin_time = $this->getLoginParam('online_time');
		$s_time = empty($onlin_time)?0:$onlin_time;
		$out_time = C("out_time");
		$out_time = empty($out_time)?180:$out_time;
		$is_common_file = $this->isCommonFile();
		if (($nowtime - $s_time) > $out_time && !$is_common_file) {
			$this->setLoginSession();
// 			$this->error('当前用户未登录或登录超时，请重新登录', "Login/index");
			$url = U('Login/index');
			$this->ajaxReturn("<script>alert('登录超时，请重新登录');window.top.location.href='".$url."';</script>","EVAL");
		} else {
			$loginInfo = $this->getLoginParam();
			$loginInfo["online_time"] = $nowtime;
			$this->setLoginSession($loginInfo);
		}
	}
	
	public function isLogin(){
		foreach ($this->_ipLevel as $key => $value){
			if($this->_userIp == $key){
				return true;
			}
		}
		$admin_id = $this->getLoginParam("admin_id");
		if(!empty($admin_id)){
			return true;
		}else{
			return false;
		}
	}
	/*
	 * 将字符串解析为数组
	 */
	public function jsonParse($jsonStr){
		if(get_magic_quotes_gpc()){
		    $jsonStr = stripcslashes($jsonStr);//如果开启了php转义，取消转义
		}
		return  (array)json_decode($jsonStr);
	}
	/**
	 * 子类实现
	 */
	public function getList(){
		$post_data = $_POST;
		$get_data = $_GET;
		$client_data = array_merge($post_data,$get_data);
		foreach ($client_data as $k=>$v){
			$this->$k  = $v;
		}
//		$this->rows = $this->rows == 10?15:$this->rows;
	}
	
	/**
	 * 根据主键删除数据
	 */
	public function del(){
		if(empty($this->$this->primary_key)){
			$this->ajaxReturn("no pripary_key");
		}
		$table = M($this->table_name,null,$this->db_name);
		$id = I($this->primary_key);
		$where = array(
				"$this->primary_key" 	=> $id,
		);
		$result = $table->where($where)->delete();
		flushMemcache();
		$this->ajaxReturn($result);
	}
	/*
	 * 传入库用户ID判断库
	 */
	public function getDb($parm){

		if( $parm <= 5000000 && $parm >= 1){
			return DB_USER_1;
		}else{
			return DB_USER_3;
		}

	}
	
	/*
	 * 设置登录信息session
	 */
	public function setLoginSession($sessionArr = array()){
		$_SESSION['loginInfo'] =$sessionArr;
	}
	/*
	 * 获取登录会话信息
	*/
	public function getLoginParam($parm = 0){
		if (empty($parm)){
			return $_SESSION['loginInfo'];
		}else{
			return empty($_SESSION['loginInfo'][$parm])?"":$_SESSION['loginInfo'][$parm];
		}
		
	}
	public function log_trace($data){
		$user_id = $this->getCurrentAdminId();
		$admin_id = empty($user_id)?"before_login_".date("H-i"):$user_id;
		$this->logWrite($data, $admin_id);
	}
	/**
	 * 写日志
	 * @param string $data
	 * @param int $user_id
	 * @param string $log_type
	 */
	public function logWrite($data,$user_id,$log_type = "default"){
		$isLog = C("is_log");
		if( $isLog ){
			if(C('app_log_path')){
				$path = rtrim(C('app_log_path'),'/').'/'.date("Ym")."/".date("d")."/".$user_id."/";
			}else{
				$path = APP_ROOT."log/".  date("Ym")."/".date("d")."/".$user_id."/";
			}
			$filename = $log_type.".txt";
			$this->createDir($path);
			$path = $path.$filename;
			if (!is_writable($path)){
				@touch($path);
			}
			/* 每段日志之前插入分隔符*/
			$head = "\r\n\r\n".date("Y-m-d H:i:s")." ".substr(microtime(), 0,3)."\r\n";
			$head .= '************************************************************'."\r\n";
			$write_data = $this->formatData($head.$data);
			$handle = @fopen($path, 'a');
			if ($handle) {
				fwrite($handle, $write_data);
				fclose($handle);
			}
		}
	}
	/**
	 * 格式转换
	 * @param object $data
	 * @return string
	 */
	public  function formatData($data){
		$return = "";
		/* 数组和对象都格式化 */
		if(is_array($data) || is_object($data)){
			foreach ($data as $key => $value) {
				if(is_array($value) || is_object($value)){
					$return .= self::formatData($value);
				}else{
					$return .= "$key=>$value\r\n";
				}
			}
		}else{
			$return .= "$data\r\n";
		}
		return $return;
	}
	/**
	 * 创建文件夹
	 * @param 文件夹路径名
	 */
	private  function createDir($path,$mode=0777,$recursive=1){
		!is_dir($path) && @mkdir($path, $mode, $recursive);
	}
	/*
	 * 获取adminId
	 */
	public function getCurrentAdminId(){
		
		return $this->getLoginParam("admin_id");
	}
	/*
	 * 初始化，验证权限
	 */
	public function init($resType = 'json') { // $resType=html
		$this->isLogin();  //检测是否登录超时
		try {
			$admin_id = $this->getLoginParam("admin_id");
			$admin_level = $this->getLoginParam('admin_level');
			if($this->permissionCheck($admin_level)){
				return true;
			}
			if (isset($admin_id)) {//No exit,login state
				if ($admin_level) {
					if (!$this->permissionCheck($admin_level)) {
// 						session_unset();
// 						session_destroy();
						$this->error("<script>alert('权限不足');</script>","",1);
					}
				} 
			}else {//Exit,the session does not exist
				$this->error("你没有登录,请登录。",U("Login/index"));
			}
		} catch (Exception $e) {
			$this->error($e->getMessage());
		}
	}
	
	// 验证权限
	public function permissionCheck($admin_level) {
// 		print_r($_SERVER["REQUEST_URI"]);exit;
		$admin_level = explode(" ", $admin_level);
		
		foreach ($admin_level as $k=>$v){
			$admin_level[$k] = trim($admin_level[$k]);
		}
		if(in_array("A", $admin_level)){
			return true;
		}
		$request_str = strtolower(__URL__);
		$is_com_file = $this->isCommonFile();
		if($is_com_file) return true;
		$req_url = substr($request_str, strpos($request_str, "index.php")+10);
		$permission = XMLToArray::createArray(file_get_contents($this->xml));
		foreach ($permission['Menu']['SubMenu'] as $subKey => $subValue) {
			foreach ($subValue['Module'] as $key => $value ){
				if(in_array($value['Permission'],$admin_level) || in_array($subValue['Permission'],$admin_level)){
					$fileName = strtolower($value['File']['Name']);
					$fileName = str_replace("/", "\/", $fileName);
					if(preg_match("/^($fileName)/", $req_url)){
						return true;
					}
					if(strpos($request_str, $fileName) !== false){
						return true;
					}
				}
			}
		}		
		return false;
	}

	private function isCommonFile(){
		$request_str = strtolower(__URL__);
		$comPath = array(
				"index/Index.html",
				"Index/changePwd",
				"showTpl/main",
				"showtpl/header",
				"showtpl/footer",
				"createMenu",
				"showtpl/password_edit",
				"Login/logOut",
				"Login/index.html",
				"Login/verify",
				"Login/login",
				"index.php/Login",
				"index.php/Index",
				"UserInfo",
		);
		//所有用户拥有公共文件访问权限
		foreach ($comPath as $each){
			$each = strtolower($each);
			if(strpos($request_str, $each) !== false){
				return true;
			}
		}
		return false;
	}
	public function exportExcel($fileName,$excelTitle,$excelCellName,$excelTableData){
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		vendor("PHPExcel.PHPExcel");
		$cellName = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'];
		$cellNum = count($excelCellName);
		$tableNum = count($excelTableData);
		$pageSize = 10000;///sheet显示条数
		$pageNum = ceil($tableNum/$pageSize);///sheet数
		$excelTableArray = $pageNum > 1 ? array_chunk($excelTableData,$pageSize) : [$excelTableData];

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("longmaster.com");
		$objPHPExcel->getProperties()->setTitle($excelTitle);
		///分页输出数据
		foreach ($excelTableArray as $page => $tableArray){
			if($page != 0){
				$objPHPExcel->createSheet($page);
			}
			$objPHPExcel->setActiveSheetIndex($page);
			$objPHPExcel->getActiveSheet()->setTitle("第".($page+1)."页");
			$cover = 0;
			if($page == 0){
				$objPHPExcel->getActiveSheet()->mergeCells("A1:{$cellName[$cellNum-1]}1")->getStyle("A1:{$cellName[$cellNum-1]}1")->getFont()->setBold(true);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1",$excelTitle);
				$cover = 1;
			}
			$cellKey = [];
			foreach ($excelCellName as $key => $value) {
				$cellKey[$key] = $value['key'];
				if (isset($value['width'])) {
					$objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$key])->setWidth($value['width']);
				} else {
					$objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$key])->setAutoSize(true);
				}
				if (isset($value['formant'])) {
					$objPHPExcel->getActiveSheet()->getStyle($cellName[$key])->getNumberFormat()->setFormatCode($value['formant']);
				}
				$objPHPExcel->getActiveSheet()->getStyle($cellName[$key] . (1+$cover))->getFont()->setBold(true);
				$objPHPExcel->setActiveSheetIndex($page)->setCellValue($cellName[$key] . (1+$cover), $value['value']);
			}
			foreach ($tableArray as $i => $row) {
				foreach ($cellKey as $j => $key) {
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue($cellName[$j] . ($i + 2 + $cover), $row[$key]);
				}
			}
			sleep(3);
		}
		///导出
		header('pragma:public');
		header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$fileName.'.xls"');
		header("Content-Disposition:attachment;filename={$fileName}_".date('YmdHis').".xls");//attachment新窗口打印inline本窗口打印
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	public function exportExcelCsv($fileName,$excelTitle,$excelCellName,$excelTableData){
		$file = "{$excelTitle}\n";
		$exportDate = date('Y-m-d H:i:s');
		$file .= "导出人：[{$this->getLoginParam()['user_name']}]\n";
		$file .= "导出时间：[{$exportDate}]\n";

		$cellKey = [];
		foreach ($excelCellName as $key => $value){
			$cellKey[$key] = $value['key'];
			$file .= "{$value['value']},";
		}
		$file .= "\n";
		foreach ($excelTableData as  $k => $tableArray){
			foreach ($cellKey as $key){
				$file .= "{$tableArray[$key]}\t,";
			}
			$file .= "\n";
		}
		header("Content-type:text/csv");
		$fileName = $this->mbConvertEncoding($fileName);
		header("Content-Disposition:attachment;filename={$fileName}_".date('YmdHis').".csv");
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		$file = $this->mbConvertEncoding($file);
		die($file);
	}

	/**
	 * 保存csv
	 * @param $excelName
	 * @param $excelTitle
	 * @param $excelCallName
	 * @param $excelTableData
	 * @param null $fileDir
	 * @return string
	 */
	public function saveExcelCsv($excelName,$excelTitle,$excelCallName,$excelTableData,$fileDir=null,$screen){
		if(!$fileDir){
			if(C('app_log_path')){
				$fileDir = rtrim(C('app_log_path'),'/').'/csv/'.date("YmdHis").'/';
			}else{
				$fileDir = APP_ROOT."log/csv/".date("YmdHis")."/";
			}
		}
		$this->createDir($fileDir);
		$excelName = $excelName.".csv";
		$fileName = $fileDir.$excelName;
		$fileName = $this->mbConvertEncoding($fileName);
		if (!is_writable($fileName)){
			@touch($fileName);
		}
		$data = [];
		$cellKey = [];
		foreach ($excelCallName as $key => $value){
			$cellKey[] = $key;
			$data[$key] = $this->mbConvertEncoding($value);
		}
		$file = fopen($fileName,"a");
		fputcsv($file,[$this->mbConvertEncoding($excelTitle)]);
		if(is_array($screen)){
			foreach ($screen as $key => $value){
				fputcsv($file,[$this->mbConvertEncoding($value)]);
			}
		}
		fputcsv($file,[$this->mbConvertEncoding("导出时间：").date("Y-m-d H:i:s")]);
		fputcsv($file,[$this->mbConvertEncoding("------------------------------ 数据 ------------------------------")]);
		fputcsv($file,$data);
		foreach ($excelTableData as $key => $value){
			foreach ($cellKey as $k){
				$data[$k] = $this->mbConvertEncoding($value[$k]."\t");
			}
			fputcsv($file,$data);
		}
		fclose($file);
		return $fileName;
	}

	/**
	 * 遍历文件夹
	 * @param $dir
	 * @return array
	 */
	public function listDir($dir){
		$result = array();
		if (is_dir($dir)){
			$file_dir = scandir($dir);
			foreach($file_dir as $file){
				if ($file == '.' || $file == '..'){
					continue;
				}
				elseif (is_dir($dir.$file)){
					$result = array_merge($result, $this->listDir($dir.$file.'/'));
				}
				else{
					array_push($result, $dir.$file);
				}
			}
		}
		return $result;
	}

	/**
	 * 文件夹压缩
	 * @param $path
	 * @param $zibName
	 */
	public function compressedFolders($path,$zibName){
		$path = rtrim($path,"/")."/";
		$fileList = $this->listDir($path);
		$fileName = $path.$zibName.".zip";
		$this->createDir($path);
		$zip = new ZipArchive();
		if($zip->open($fileName,ZipArchive::OVERWRITE) === true){
			foreach ($fileList as $value){
				$zip->addFile($value,strtr($value,[$path=>""]));
			}
		}
		$zip->close();
		header ( "Cache-Control: max-age=0" );
		header ( "Content-Description: File Transfer" );
		header ( 'Content-disposition: attachment; filename=' . $zibName."_".date("YmdHis").".zip" ); // 文件名
		header ( "Content-Type: application/zip" ); // zip格式的
		header ( "Content-Transfer-Encoding: binary" ); // 告诉浏览器，这是二进制文件
		header ( 'Content-Length: ' . filesize ( $fileName ) );
		@readfile($fileName);
		@unlink($fileName);
	}

	/**
	 * 文件压缩
	 * @param $fileName
	 * @param $data
	 * @param null $fileDir
	 */
	public function compressedFile($fileName,$data,$fileDir=null){
		$fileName = $this->mbConvertEncoding($fileName);
		if(!$fileDir){
			if(C('app_log_path')){
				$fileDir = rtrim(C('app_log_path'),'/').'/csv/'.date("YmdHis").'/';
			}else{
				$fileDir = APP_ROOT."log/csv/".date("YmdHis")."/";
			}
		}
		$this->createDir($fileDir);
		$zip = new ZipArchive();
		$file = $fileDir.$fileName.".zip";
		if($zip->open($file,ZipArchive::OVERWRITE) === true){
			foreach ($data as $name => $filePath){
				$zip->addFile($filePath,strtr($filePath,[$fileDir=>""]));
			}
			$zip->close();
		}
		header ( "Cache-Control: max-age=0" );
		header ( "Content-Description: File Transfer" );
		header ( 'Content-disposition: attachment; filename=' . $fileName."_".date("YmdHis").".zip" ); // 文件名
		header ( "Content-Type: application/zip" ); // zip格式的
		header ( "Content-Transfer-Encoding: binary" ); // 告诉浏览器，这是二进制文件
		header ( 'Content-Length: ' . filesize ( $file ) );
		@readfile($file);
		@unlink($file);
	}
	public function mbConvertEncoding($string,$encode="GB2312"){
		$encodeN = mb_detect_encoding($string, ["ASCII","UTF-8","GB2312","GBK","BIG5"]);
		$string = $encodeN == $encode ? $string : mb_convert_encoding($string,$encode,$encodeN);
		return $string;
	}

	/**
	 * 字符串加星隐藏
	 * @param $string
	 * @param null $start
	 * @param int $end
	 * @param string $dot
	 * @param string $charset
	 * @return string
	 */
	public function hideString($string,$start=null,$end=0,$dot="*",$charset="UTF-8"){
		$encode = mb_detect_encoding($string, ["ASCII","UTF-8","GB2312","GBK","BIG5"]);
		$string = $encode == $charset ? $string : mb_convert_encoding($string,$charset,$encode);
		$stringLength = mb_strlen($string,$charset);
		if($stringLength <= $start) return $string;
		if($stringLength <= 1) return $string;
		if($start !== null){
			$start = $start == 0 ? 1 : $start;
			$end = $end == 0 || $end > ($stringLength - 1) ? $stringLength - 1 : $end;
			$str = mb_substr($string,0,$start,$charset);
			for ($i=$start;$i<=$end;$i++){
				$str .= $dot;
			}
			if($end+1 < $stringLength) {
				$str .= mb_substr($string, $end + 1,$stringLength-$end-1,$charset);
			}
		}else{
			if($stringLength == 2){
				$str = $dot.mb_substr($string,1,1,$charset);
			}else{
				$str = mb_substr($string,0,1,$charset);
				for($i=1;$i<$stringLength-1;$i++){
					$str .= "*";
				}
				$str .= mb_substr($string,-1,1,$charset);
			}
		}
		return $str;
	}
	public function exportExcelTxt($txtTitle,$txtHead,$txtContent,$encode="GB2312"){
		$file = "";
		foreach ($txtHead as $key => $value){
			$file .= $this->mbConvertEncoding(trim($value),$encode).chr(9);
		}
		$file = rtrim($file,chr(9));
		$file .= chr(13).chr(10);
		foreach ($txtContent as $key => $value){
			foreach ($value as $k => $va){
				$file .= $this->mbConvertEncoding(trim($va),$encode).chr(9);
			}
			$file = rtrim($file,chr(9));
			$file .= chr(13).chr(10);
		}
		header("Content-type:text/csv; charset={$encode}");
		$fileName = $this->mbConvertEncoding($txtTitle);
		header("Content-Disposition:attachment;filename={$fileName}.txt");
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		die($file);
	}
}