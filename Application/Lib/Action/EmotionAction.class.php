<?php
/**
 * @example      亲情账号
 * @file         EmotionAction.class.php
 * @author       Shenliang.Xue<shenliang.xue@longmaster.com.cn>
 * @version      v1.0
 * @date         2016/7/14
 * @time         17:52
 * @copyright © 2016, Longmaster Corporation. All right reserved.
 */
class EmotionAction extends CommonAction{
    public function index(){
        $this->assign("timeFor",date("Y-m-d",strtotime("-7 day")));
        $this->assign("timeTo",date("Y-m-d",strtotime("-0 day")));
//图片
        import("@.CustomLib.FileClass");
        $hfs = new HfsModel(HfsModel::OP_TYPE_ID_CARD);
        $url = $hfs->getDownUrl();
        $this->assign("idCardUrl",$url);
        $this->display();
    }

//遍历数据库中的数据
    public function getList(){
        parent::getList();
        $table = M("t_user_patient_info",null,DB_REGISTRATION_NET);
        $where = [
            "auth_status IN({$this->auth_status})",
        ];
       switch($this->time_frame){
            case 1:
                array_push($where,"DATE_FORMAT(apply_dt,'%Y-%m-%d') BETWEEN '{$this->time_for}' AND '{$this->time_to}'");
                break;
            case 2:
                array_push($where,"DATE_FORMAT(update_dt,'%Y-%m-%d') BETWEEN '{$this->time_for}' AND '{$this->time_to}'");
                array_push($where,"auth_state IN(6)");
                break;
            case 3:
                array_push($where,"DATE_FORMAT(update_dt,'%Y-%m-%d') BETWEEN '{$this->time_for}' AND '{$this->time_to}'");
                array_push($where,"auth_state IN(4,7)");
                break;
        }
        $field = [
            "id",
            "id_photo",
            "auth_status",
            "CASE auth_status WHEN 1 THEN '待审核' WHEN 2 THEN '审核中' WHEN 3 THEN '人工审核通过' WHEN 4 THEN '人工审核失败' WHEN 5 THEN '认证中' WHEN 6 THEN '认证成功'WHEN 7 THEN '认证失败'END AS auth_status_name",
            "user_id",
            "patient_name",
            "nation",
            "patient_id_no",
            "CASE patient_sex WHEN 0 THEN '男' WHEN 1 THEN '女'END AS patient_sex",
            "patient_birthday",
            "patient_phone",
            "remarks",
            "opt_admin",
            "DATE_FORMAT(update_dt,'%Y-%m-%d') AS update_dt",
            "REPLACE(patient_addr,'>','') AS patient_addr"
        ];
        $list = $table->where($where)->field($field)->page($this->page,$this->rows)->order("insert_dt DESC,auth_status ASC,id ASC")->select();
        $list = is_array($list) ? $list : [];
//        获取记录条数
        $total = $table->where($where)->count();
        $data = [
            "rows"  => $list,
            "total" => $total,
        ];
        $this->ajaxReturn($data);
    }

    public function audit()
    {
        parent::getList();
// 写日志表
        $db = M("t_user_patient_info",null,DB_REGISTRATION_NET);
        $log = $db->where("id={$this->id}")->find();
        $log["log_dt"] = date("Y-m-d H:i:s");
        $dbLog = M("t_log_user_patient_info",null,DB_LOG_REGISTRATION_NET);
        $dbLog->add($log);
        $user = $this->getLoginParam("user_name");
        $data = [
            "op_admin"       => $user,
            "auth_status"    => $this->auth_status,
           "id"               => $this->id,
        ];
        if($this->auth_status == 4){
            $data["remarks"] = $this->remarks;
            $data['reason'] = $this->reason;
        }else{
            $data["remarks"] = "通过审核";
            $data['reason'] = "";
        }
       $result = $db->save($data);
//        认证成功
        if($result){
            if($this->auth_status == 3){
                $result = $this->_port($log['patient_id_no'],$log['patient_name'],$log['id'],$log['user_id']);
            }else{
                import("@.CustomLib.Push");
                import("@.CustomLib.ArrayToXML");
                import("@.CustomLib.Common");
                import("@.CustomLib.CryptAES");
             if($this->reason){
                   $p3 = substr($log['patient_id_no'],-4);
                   $content ="您实名认证的亲情账户({p2}尾号{p3})未通过我们的审核，请确保您提供的身份信息的准确性后，重新申请认证。谢谢您使用贵健康！";
                   $sms="您在{p1}实名认证的亲情账户({p2}尾号{p3})未通过我们的审核，请确保您提供的身份信息的准确性后，通过{p1}重新申请认证。谢谢您使用{p1}！";
             }
                   $st = strtr($sms,["{p1}"=>"贵健康","{p2}"=>"身份证","{p3}"=>$p3]);
                   $con = strtr($content,["{p2}"=>"身份证","{p3}"=>$p3]);
                $res = Push::sendCommonNotice($log['user_id'],'亲情账户实名认证审核进度','亲情账户实名认证审核进度，请查看。',$con,'认证','#09cc9f');
                Push::SMSPush([$log['patient_phone']],$st);
                if($res){
                    $result = [
                        "code"      => 0,
                        "message"   => "推送成功"
                    ];
                }else{
                    $result = [
                        "code"      => -1,
                        "message"   => "推送异常"
                    ];
                }
            }
        }else{
            $result = [
                "code" => -2,
                "message" => "更新数据库异常"
            ];
        }
        $this->ajaxReturn($result);
    }

    private function _port($id_number,$real_name,$patient_id,$user_id){
        parent::getList();
        if($this->auth_status == 3) {
            $url = rtrim(C("api_cfg.base_url"), "/") . "/family/realname";
            $request = [
                "id_number"     => $id_number,
                "real_name"     => $real_name,
                "patient_id"    => $patient_id,
            ];
            $json = json_encode($request);
            import("@.CustomLib.Common");
            import("@.CustomLib.CryptAES");
            $key = C("push_cfg.aes_key");
            $str = Common::aes_encrypt($json, $key);  //加密
            $request = [
                "user_id" =>1,
                "gender"=>0,
                "c_type"=>1,
                "c_ver"=>0,
                "task_id"=>0,
                "sid"=>0,
                "pid"=>0,
                "auth_info" => base64_encode($str),
            ];
            $result = Common::post($url, ["json"=>json_encode($request)]);
            $this->logWrite(json_encode(['result'=>$result,'request'=>$request]),$user_id,"authen_log");
            $result = json_decode($result, true);
            return $result;
        }
    }

}