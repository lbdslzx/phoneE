<?php
/**
 * @example      消息推送
 * @file         PushAction.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2016/1/20 0020
 * @time         11:42
 */

class PushAction extends CommonAction{
    /**
     * 重定义构造方法
     * PushAction constructor.
     */
    public function __construct(){
        parent::__construct();
        import("@.CustomLib.Push");
        import("@.CustomLib.ArrayToXML");
        import("@.CustomLib.Common");
        import("@.CustomLib.CryptAES");
    }

    /**
     * 消息列表页
     */
    public function index(){
        $table_title = [
            'temp_id'                   => '模板ID',
            'message_title'             => '消息标题',
            'lable_name'                => '消息标签',
            'push_state'                => '推送状态',
            'upd_dt'                    => '推送时间',
            'push_type'                 => '推送类型',
            'order'                     => '操作指示',
        ];
        $this->assign("table_title",$table_title);//列表页文章标题
        $this->assign("lable",$this->_getLableList());
        $this->display();
    }

    /**
     * 获取消息标签列表
     * @return mixed
     */
    private function _getLableList(){
        $db = M("t_user_message_template",null,DB_WEB);
        $list = $db->where('temp_type = 2 AND message_type = 0 AND is_delete = 0')
            ->distinct('lable_name')
            ->field('lable_name')
            ->select();
        return $list;
    }

    /**
     * 异步获取消息列表
     */
    public function getList(){
        parent::getList();
        $db = M("t_user_message_template",null,DB_WEB);

        $where = "temp_type = 2 AND message_type = 0 AND is_delete = 0";
        if(isset($this->lable_name) && $this->lable_name != "全部"){
            $where .= " AND lable_name = '{$this->lable_name}'";
        }
        if(isset($this->push_state) && $this->push_state > 0){
            $where .= " AND push_state = {$this->push_state}";
        }
        if(isset($this->message_title) && trim($this->message_title) != ''){
            $message_title = trim($this->message_title);
            $where .= " AND message_title LIKE '%{$message_title}%'";
        }

        $totalRecords = $db->where($where)->count();
        $result = $db->where($where)->order('upd_dt DESC,temp_id DESC')
            ->page($this->page,$this->rows)
            ->select();
        $list = [];
        foreach($result as $key => $value){
            $list[] = [
                'temp_id'       => $value['temp_id'],
                'message_title' => $value['message_title'],
                'lable_name'    => "<div style='width:100px;height: 24px;line-height: 24px;color: #ffffff;font-weight:bold;background-color: {$value['lable_color']};margin: 0 auto;'>{$value['lable_name']}</div>",
                'push_state'    => $value['push_state'] == 1 ? '未推送' : ($value['push_state'] == 2 ? '推送中' : '已推送'),
                'upd_dt'        => $value['upd_dt'],
                'push_type'     => $value['client_type'] == 0 ? '个推' : ($value['client_type'] == 1 ? '贵州版' : ($value['client_type'] == 2 ? '全国版' : '贵州版+全国版')),
                'order'         => $value['push_state'] == 1 ? "<a href='javascript:void(0);' onclick='goSend({$value['temp_id']},{$value['client_type']})'>推送</a>&nbsp;<a href='edit?id={$value['temp_id']}'>编辑</a>&nbsp;<a href='javascript:void(0);' onclick='del({$value['temp_id']})'>删除</a>" : "<a href='see?id={$value['temp_id']}'>查看</a>",
            ];
        }
        $table_data["total"]    = $totalRecords;
        $table_data["rows"]     = $list;
        $this->ajaxReturn($table_data);
    }

    /**
     * 消息编辑
     */
    public function edit(){
        parent::getList();
        $db = M("t_user_message_template",null,DB_WEB);

        if(IS_POST){
            $input = $_POST;
            if($input['action_state'] == 1){
                $extend = [
                    'action'=>$input['action'],
                    'params'=>[
                        'url'       => $input['url'],
                        'valid_dt'  => strtotime($input['valid_dt'])
                    ]
                ];
                $extend = json_encode($extend);
            }else{
                $extend = [
                    'action'=> '',
                    'params'=>[
                        'url'       => '',
                        'valid_dt'  => 0
                    ]
                ];
                $extend = json_encode($extend);
            }

            $color = $input['radio_type'] == 1 ? $this->_getLableColor($input['msg_type']) : $input['lable_color'];
            $lable = $input['radio_type'] == 1 ? $input['msg_type'] : $input['lable_name'];

            $common = new CommonAction();
            $adminId = $common->getCurrentAdminId();

            $tempId = ($input['temp_id']) ? $input['temp_id'] : $db->max('temp_id')+1;

            if($input['user_all'] !=1){
                $mobile = $input['phone_num'];
                $this->_userPhoneWriter($tempId,$mobile,'news',$input['is_user_id']);
            }

            $data = [
                'temp_id'           =>  $tempId,
                'client_type'       =>  $input['client_type'],
                'phone_num'         =>  $input['user_all'] == 1 ? 0 : '',
                'temp_type'         =>  2,
                'message_title'     =>  $input['message_title'],
                'message_abstract'  =>  $input['message_abstract'],
                'message_details'   =>  $input['message_details'],
                'lable_name'        =>  $lable,
                'lable_color'       =>  $color,
                'message_type'      =>  0,
                'push_state'        =>  $input['send'] == 1 ? 2 : 1,
                'is_delete'         =>  0,
                'upd_user_id'       =>  $adminId,
                'upd_dt'            =>  date('Y-m-d H:i:s'),
                'extend'            =>  $extend
            ];
            if($input['temp_id']){
                $result = $db->save($data);
            }else{
                $data['create_user_id'] = $adminId;
                $data['create_dt']      = date('Y-m-d H:i:s');
                $result = $db->add($data);
            }
            ///推送
            if($result && $input['send'] == 1){
                $this->_messagePush($tempId,$input['client_type']);
            }
            //flushMemcache();
            $this->ajaxReturn($result);
        }

        $result = $this->id ? $db->where("temp_id = {$this->id}")->find() : array('client_type'=>3);
        $extend = (isset($result['extend']) && $result['extend'] != '') ? json_decode($result['extend'],true) : null;

        $mobile = $this->_getUserPhoneForFile('news',@$result['temp_id']);
        list($mobile,$isUserId) = explode("#",$mobile);

        $detail = [
            'temp_id'           => @$result['temp_id'],
            'client_type'       => intval(@$result['client_type']),
            'phone_num'         => $mobile == 0 ? '' : $mobile,
            'message_title'     => @$result['message_title'],
            'message_abstract'  => @$result['message_abstract'],
            'message_details'   => @$result['message_details'],

            'lable_name'        => isset($result['lable_name']) ? $result['lable_name'] : "公告",
            'lable_color'       => isset($result['lable_color']) ? $result['lable_color'] : "#FFAC36",

            'action_state'      => (is_array($extend) && $extend['action'] != '') ? 1 : 0,
            'action'            => @$extend['action'],
            'url'               => @$extend['params']['url'],
            "is_user_id"        => intval($isUserId),
            'valid_dt'          => isset($extend['params']['valid_dt']) ? date('Y-m-d H:i:s',$extend['params']['valid_dt']) : '',//date('Y-m-d H:i:s',strtotime("+14 day"))
        ];
        $this->assign('detail',$detail);
        $this->display();
    }

    /**
     * 加推送的用户手机号写入文件中
     * @param $tempId   消息ID
     * @param $mobile   手机号
     * @param $file     文件 消息：news 活动：activity 提醒：remind
     * @return int
     */
    private function _userPhoneWriter($tempId,$mobile,$file,$isUserId=0){
        $dir = C("push_cfg.file_dir");
        $dir = rtrim($dir,'/')."/mobile/{$file}/";
        if(!is_dir($dir)){
            mkdir($dir,0777,true);
        }
        return file_put_contents($dir.$tempId.".txt",$mobile."#".$isUserId);
    }

    /**
     * 删除文件
     * @param $tempId
     * @param $file
     * @return bool
     */
    private function _userPhoneDel($tempId,$file){
        $dir = C("push_cfg.file_dir");
        $dir = rtrim($dir,'/')."/mobile/{$file}/{$tempId}.txt";
        return @unlink($dir);
    }

    /**
     * 获取文件
     * @param $file
     * @param $tempId
     * @return bool|int|string
     */
    private function _getUserPhoneForFile($file,$tempId){
        $dir = C("push_cfg.file_dir");
        $dir = rtrim($dir,'/')."/mobile/{$file}/{$tempId}.txt";
        return file_exists($dir) ? file_get_contents($dir) : 0;
    }

    /**
     * 删除推送的消息
     */
    public function del(){
        parent::getList();
        $db = M("t_user_message_template",null,DB_WEB);
        $id = $_POST["id"];
        $data = array(
            "temp_id" 	=> $id,
            'is_delete'     => 1
        );
        $result = $db->save($data);
        $this->_userPhoneDel($id,'news');
        //flushMemcache();
        $this->ajaxReturn($result);
    }

    /**
     * 获取预定义颜色
     * @param $name  标签名称
     * @return null  对应颜色
     */
    private function _getLableColor($name){
        $lable = [
            '公告' => '#FFAC36',
            '活动' => '#41C3BE',
            '提醒' => '#F8694B'
        ];
        return isset($lable[$name]) ? $lable[$name] : null;
    }

    /**
     * 消息推送
     * @return bool
     */
    public function messagePush(){
        parent::getList();
        if($this->id){
            $result = $this->_messagePush($this->id,$this->type);
        }else{
            $result = -1;
        }
        $this->ajaxReturn($result);
    }

    /**
     * 消息推着
     * @param $tempId
     * @param $clientType
     * @return bool
     */
    private function _messagePush($tempId,$clientType = 1){
        $db = M("t_user_message_template",null,DB_WEB);
        $data = array(
            "temp_id" 	    => $tempId,
            'push_state'    => 2
        );
        $db->save($data);

        $mobile = $clientType == 0 ? $this->_getUserPhoneForFile('news',$tempId) : 0;
        $userIds = $mobile == 0 ? 0 : $this->_getUserIds($mobile);
        $result = Push::messagePushByTemp($userIds,$tempId,$clientType);
        if($result){
            return true;
        }else{
            $data = array(
                "temp_id" 	    => $tempId,
                'push_state'    => 1
            );
            $db->save($data);
            return false;
        }
    }

    /**
     * 通过手机号获取用户ID
     * @param $mobile
     * @return array
     */
    private function _getUserIds($mobile){
        list($mobile,$isUserId) = explode("#",$mobile);
        $mobileArr = explode(',', $mobile);
        if($isUserId == 1){
            return $mobileArr;
        }
        $mobile = implode("','", $mobileArr);
        $mobile = "'" . $mobile . "'";
        $db = M("t_user_phone", null, DB_QUERY);
        $list = $db->where("REPLACE(phone_num,'+86','') IN ({$mobile})")
            ->distinct('user_id')
            ->field('user_id')
            ->select();
        $userIds = [];
        foreach ($list as $key => $value) {
            $userIds[] = $value['user_id'];
        }
        return $userIds;
    }

    public function see(){
        parent::getList();
        $db = M("t_user_message_template",null,DB_WEB);
        $detail = $db->where("temp_id = {$this->id}")->find();
        $extend = (isset($detail['extend']) && $detail['extend'] != '') ? json_decode($detail['extend'],true) : null;
        $detail['action_state'] = (is_array($extend) && $extend['action'] != '') ? 1 : 0;
        $detail['action']       = (@$extend['action'] == 'open_web') ? "URL" : "APP功能点";
        $detail['url']          = @$extend['params']['url'];
        $detail['valid_dt']     = (@$extend['params']['valid_dt'] == 0) ? "长期有效" : date('Y-m-d H:i:s',$extend['params']['valid_dt']);//date('Y-m-d H:i:s',strtotime("+14 day"))
        if($detail['client_type'] == 0){
            $mobile = $this->_getUserPhoneForFile("news",$detail['temp_id']);
            list($mobile,$isUserId) = explode("#",$mobile);
            if($isUserId == 1){
                $detail['phone_num'] = "个推 用户ID：". $mobile;
            }else{
                $detail['phone_num'] = "个推 手机号：". $mobile;
            }
        }else{
            $detail['phone_num'] = $detail['client_type'] == 1 ? '贵州版' : ($detail['client_type'] == 2 ? '全国版' : "贵州版");
        }
        $this->assign('detail',$detail);
        $this->display();
    }
}