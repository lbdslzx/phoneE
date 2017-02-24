<?php

class FileUploadAction extends Action {
	
	public function index(){
		
	}
	function __construct(){
			if(!class_exists("FileClass")){
				import("@.CustomLib.FileClass");
			}
			parent::__construct();
	}
	public function upload(){
		$current_url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["SCRIPT_NAME"];
		$relative_path = date(Ymd)."/";
		$upload_dir = C("hfs_ivr_file_path").$relative_path;
		$file_name = rand(0, 9999999).time().$_FILES["file"]["name"];
		$file_path = $upload_dir.$file_name;
		$stream=file_get_contents('php://input')?file_get_contents('php://input'):file_get_contents($_FILES['file']['tmp_name']);
		$result = FileClass::streamUpload($stream,$file_path);
		$url_file_path = urlencode($file_path);
		if($result === 0){
			echo "<img id='pic' src='$current_url/FileUpload/viewPhoto/?file=$relative_path$file_name' width='200px' height='200px'/>";
		}else{
			echo "上传失败";
		}
	}
	public function uploadUseRelative(){
		$current_url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["SCRIPT_NAME"];
		$relative_path = "upload/".date(Ymd)."/";
		$upload_dir = APP_ROOT.$relative_path;
		$file_name = rand(0, 9999999).time().$_FILES["file"]["name"];
		$file_path = $upload_dir.$file_name;
		$stream=file_get_contents('php://input')?file_get_contents('php://input'):file_get_contents($_FILES['file']['tmp_name']);
		$result = FileClass::streamUpload($stream,$file_path);
		$url_file_path = urlencode($file_path);
		if($result === 0){
			echo "
					<img id='pic' src='$current_url/../$relative_path$file_name' width='200px' height='200px'/>
					<input id='pic_addr' type='hidden' value='$relative_path$file_name'/> 
			";
		}else{
		echo "上传失败";
		}
		}
	public function viewPhoto(){
		$path = $_GET["file"];
		header("Content-Type:image/jpeg");
		$upload_dir = dirname(C("UPLOAD_DIR")).DIRECTORY_SEPARATOR;
		$path = $upload_dir.$path;
		FileClass::downloadFile($path);
	}
}