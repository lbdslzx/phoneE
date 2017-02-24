<?php
/**
 * @example      合作药店
 * @file         DrugstoreManAction.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2016-06-13
 * @time         17:16
 * @copyright © 2016, Longmaster Corporation. All right reserved.
 */
class DrugstoreManAction extends CommonAction{
    private $_province,$_tableName='t_coop_pharmacy_info',$_picTableName='t_coop_pharmacy_pic',$_dbName=DB_WEB;
    public function __construct(){
        parent::__construct();
        $province = file_get_contents(RESOURCE_PUBLIC."json/Province.json");
        $this->_province = json_decode($province,true);
    }

    public function index(){
        $this->assign("province",$this->_province);
        $this->display();
    }
    public function getList(){
        parent::getList();
        $where = ["is_delete=0"];
        if(!empty($this->pharmacy_name)){
            $where[] = "pharmacy_name LIKE '%{$this->pharmacy_name}%'";
        }
        if(!empty($this->pharmacy_tel)){
            $where[] = "pharmacy_tel LIKE '%{$this->pharmacy_tel}%'";
        }
        $province = isset($this->province) ? $this->province : -1;
        if($province >= 0){
            $where[] = "province_id = {$this->province}";
        }
        $field = [
            'pharmacy_id',
            'pharmacy_addr',
            'pharmacy_name',
            'pharmacy_tel',
            'pharmacy_logo',
            'upd_dt',
            "(SELECT COUNT(*) FROM {$this->_picTableName} WHERE pharmacy_id = a.pharmacy_id AND is_delete = 0) AS count",
            "province_name",
            'id AS sort'
        ];
        $table = M("{$this->_tableName} a",null,$this->_dbName);
        $list = $table->where($where)
            ->field($field)
            ->order("id DESC,pharmacy_id DESC")
            ->page($this->page,$this->rows)
            ->select();
        $total = $table->where($where)->count();
        $this->ajaxReturn(['total'=>$total,'rows'=>(array)$list]);
    }
    public function del(){
        parent::getList();
        $table = M($this->_tableName,null,$this->_dbName);
        $data = [
            'pharmacy_id'   => $this->id,
            'is_delete'     => 1
        ];
        $result = $table->save($data);
        $this->ajaxReturn($result);
    }
    public function upSort(){
        parent::getList();
        $table = M($this->_tableName,null,$this->_dbName);
        $data = [
            'pharmacy_id'   => $this->pharmacy_id,
            'id'            => $this->id
        ];
        $result = $table->save($data);
        $this->ajaxReturn($result);
    }
    public function edit(){
        parent::getList();
        $pharmacyId = intval(@$this->id);
        $table = M($this->_tableName,null,$this->_dbName);
        $picTable = M($this->_picTableName,null,$this->_dbName);
        $user = $this->getLoginParam("user_name");
        //$user = $_SESSION['loginInfo']['user_name'];
        if(IS_POST){
            $input = $_POST;
            $pharmacyId = $input['pharmacy_id'] ? $input['pharmacy_id'] : $table->max("pharmacy_id") + 1;
            list($longitude,$latitude) = explode(",",$input['map']);
            $data = [
                'pharmacy_id'   => $pharmacyId,
                'pharmacy_name' => $input['pharmacy_name'],
                'pharmacy_addr' => $input['pharmacy_addr'],
                'pharmacy_tel'  => $input['pharmacy_tel'],
                'pharmacy_logo' => $input['pharmacy_logo'],
                'is_delete'     => 0,
                'remark'        => '',
                'province_id'   => $input['province'],
                'province_name' => $this->getProvinceName($input['province']),
                "has_promotions"=> intval($input['has_promotions']),
                "has_delivery"  => intval($input['has_delivery']),
                "longitude"     => $longitude,
                "latitude"      => $latitude
            ];
            if(!$input['pharmacy_id']){
                $data['id']             = 0;
                $data['creator']        = $user;
                $data['create_dt']      = date('Y-m-d H:i:s');
                $result = $table->add($data);
            }else{
                $data['upd_user']       = $user;
                $data['upd_dt']         = date('Y-m-d H:i:s');
                $result = $table->save($data);
            }
            foreach ($input['pics'] AS $key => $value){
                list($pic,$bigPic) = explode(",",$value);
                $data = [
                    "pharmacy_id"   => $pharmacyId,
                    "pharmacy_name" => $input['pharmacy_name'],
                    "pic_id"        => ($picTable->where("pharmacy_id={$pharmacyId}")->max("pic_id")+1),
                    "pic"           => $pic,
                    "big_pic"       => $bigPic,
                    "is_delete"     => 0
                ];
                $picTable->add($data);
            }
            $this->ajaxReturn($result);
        }
        $where = [['pharmacy_id'=>$pharmacyId,'is_delete'=>0]];
        $info = $table->where($where)->find();
        $picField = [
            'pic',
            'pic_id'
        ];
        $pic = $picTable->where($where)->field($picField)->select();
        $this->assign("info",$info);
        $this->assign("pic",$pic);
        $this->assign("province",$this->_province);
        $this->display();
    }
    public function getProvinceName($id){
        $province = $this->_province;
        foreach ($province as $value){
            if($value['id'] == $id){
                return $value['name'];
            }
        }
        return "其他";
    }
    public function delPic(){
        parent::getList();
        $picTable = M($this->_picTableName,null,$this->_dbName);
        $data = [
            'is_delete'     => 1
        ];
        $where = [
            'pharmacy_id'   => $this->id,
            'pic_id'        => $this->pid,
        ];
        $result = $picTable->where($where)->save($data);
        $this->ajaxReturn($result);
    }
    public function map(){
        parent::getList();
        $ip = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
        $ip = ($ip) ? $ip : $_SERVER["REMOTE_ADDR"];
        $ip = $ip == "127.0.0.1" ? "222.85.144.70" : $ip;
        $url = "http://restapi.amap.com/v3/ip?key=f7fbe536ab467c6aa2a65bc71441f74d&ip={$ip}";
        $result = file_get_contents($url);
        $result = json_decode($result,true);
        $map = $this->map;
        list($ipMap,$ipMap1) = explode(";",$result['rectangle']);
        $map = (empty($map) or $map == ",") ? $ipMap : $map;
        $this->assign("address",$this->address);
        $this->assign("map",$this->map == "," ? "" : $this->map);
        $this->assign("lonLat",$map);
        $this->display("amap");
    }
    public function uploadPic(){
        parent::getList();
        import("@.CustomLib.FileClass");
        $fileId=$this->file_id;
        $stream = file_get_contents($_FILES[$fileId]["tmp_name"]);
        $imgType = strtr($_FILES[$fileId]['type'],["image/"=>""]);
        if(!in_array($imgType,["png","gif","jpg","jpeg"])){
            $result = [
                'code'          => -1,
                "img_url"       => "",
                "message"       => "请上传jpg/png/gif格式的图片！"
            ];
            $this->ajaxReturn($result);
        }
        $imgType = $imgType == "jpeg" ? "jpg" : $imgType;
        $fileName = getMillisecond().".{$imgType}";
        $fileAddress = C("yd_image");
        $imageAddress = $fileAddress = formateURIAddr($fileAddress);
        $fileAddress .= $fileName;
        FileClass::streamUpload($stream, $fileAddress);
        $hfs = new HfsModel(HfsModel::OP_TYPE_YD_IMG);
        $hfs->addParm("file_name", $fileName);
        //$uploadAddress = $hfs->getJson();
        $downAddress = $hfs->getDownUrl();
        //$fileName = rtrim($downAddress,"/")."/".$fileName."/".microtime(true);
        import('@.CustomLib.Image');
        $imageInfo = Image::getImageInfo($fileAddress);
        if($fileId == "file_logo"){
            if($imageInfo['width'] != $imageInfo['height']){
                $result = [
                    'code'          => -1,
                    "img_url"       => "",
                    "message"       => "药店头像尺寸有误，正确尺寸1:1且尺寸不能小于：115px * 115px"
                ];
            }elseif ($imageInfo["width"] < 115){
                $result = [
                    'code'          => -1,
                    "img_url"       => "",
                    "message"       => "药店头像尺寸有误，最小尺寸：115px * 115px"
                ];
            }else{
                $thumbFileName = getMillisecond().".{$imageInfo['type']}";
                $thumbAddress = rtrim($imageAddress,"/")."/".$thumbFileName;
                Image::thumb($fileAddress,$thumbAddress,115,115,true);
                $fileName = rtrim($downAddress,"/")."/".$thumbFileName."/".microtime(true);
                $result = [
                    'code'          => 0,
                    "img_url"       => $fileName,
                    "message"       => "图片上传成功"
                ];
            }
        }else{
            if($imageInfo["width"]/$imageInfo['height'] != 544/626){
                $result = [
                    'code'          => -1,
                    "img_url"       => "",
                    "message"       => "图片尺寸有误，正确尺寸544:628且尺寸不能小于：544px * 626px"
                ];
            }elseif($imageInfo["width"] < 544){
                $result = [
                    'code'          => -1,
                    "img_url"       => "",
                    "message"       => "图片尺寸有误，尺寸不能小于：544px * 626px"
                ];
            }else{
                $thumbFileName = getMillisecond().".{$imageInfo['type']}";
                $thumbAddress = rtrim($imageAddress,"/")."/".$thumbFileName;
                Image::thumb($fileAddress,$thumbAddress,544,626,true);
                $fileName = rtrim($downAddress,"/")."/".$thumbFileName."/".microtime(true);
                
                $thumbFileName = getMillisecond().".{$imageInfo['type']}";
                $thumbAddress = rtrim($imageAddress,"/")."/".$thumbFileName;
                Image::thumb($fileAddress,$thumbAddress,275,316,true);
                $thumbFileName = rtrim($downAddress,"/")."/".$thumbFileName."/".microtime(true);
                $result = [
                    'code'          => 0,
                    "img_url"       => $thumbFileName,
                    "big_img_url"   => $fileName,
                    "message"       => "图片上传成功"
                ];
            }
        }
        unlink($fileAddress);
        $this->ajaxReturn($result);
    }
}