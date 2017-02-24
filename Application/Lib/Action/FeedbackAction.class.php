<?php
/**
 * @example      用户反馈
 * @file         FeedbackAction.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2016-01-29
 * @time         14:35
 */
class FeedbackAction extends CommonAction{
    public function __construct(){
        parent::__construct();
        import("@.CustomLib.Push");
        import("@.CustomLib.ArrayToXML");
        import("@.CustomLib.Common");
        import("@.CustomLib.CryptAES");
    }

    public function index(){
        $table_title = [
            'fb_id'                 => '编号',
            'name'                  => '用户姓名',
            'phone_number'        => '联系电话',
            'phone_type'           => '手机型号',
            'phone_os_version'    => '手机系统版本',
            'rom_version'          => '软件版本',
            'content'              => '反馈内容',
            'log_dt'                => '提交时间',
            'order'                 => '操作指示',
        ];
        $this->assign("table_title",$table_title);//列表页文章标题
        $this->display();
    }
    public function getList(){
        parent::getList();
        $db = M("t_client_feedback",null,DB_ADMIN);

        $where = "1 = 1";
        if($this->phone_number){
            $phone_number = "+86".substr($this->phone_number,-11);
            $where .= " AND phone_number = {$phone_number}";
        }
        if($this->name){
            $where .= " AND name LIKE '%{$this->name}%'";
        }
        $totalRecords = $db->where($where)->count();
        $result = $db->where($where)->order('fb_id DESC')
            ->page($this->page,$this->rows)
            ->select();
        $list = [];
        foreach($result as $key => $value){
            $order = "<a href='see?id={$value['fb_id']}'>查看</a>";
            if($value['deal_state'] == 2){
                $order .= "&nbsp;<a href='javascript:void(0);' onclick=\"openW({$value['fb_id']})\">回复</a>";
            }else{
                $order .= "&nbsp;<a disabled>已回复</a>";
            }

            $list[] = [
                'fb_id'             => $value['fb_id'],
                'name'              => $value['name'],
                'phone_number'      => strtr($value['phone_number'],["+86"=>""]),
                'email_addr'        => $value['email_addr'],
                'phone_type'        => $value['phone_type'],
                'phone_type'        => $value['phone_type'],
                'phone_os_version'  => $value['phone_os_version'],
                'rom_version'       => $value['rom_version'],
                'content'           => "<a title='{$value['content']}'>{$value['content']}</a>",
                'log_dt'            => $value['log_dt'],
                'order'             => $order
            ];
        }
        $table_data["total"]    = $totalRecords;
        $table_data["rows"]     = $list;
        $this->ajaxReturn($table_data);
    }
    public function see(){
        parent::getList();
        $db = M("t_client_feedback",null,DB_ADMIN);
        $detail = $db->where("fb_id = {$this->id}")->find();
        $this->assign('detail',$detail);
        $this->display();
    }

    public function getFbInfo(){
        parent::getList();
        $fb_id = $_POST['fb_id'];
        $db = M("t_client_feedback",null,DB_ADMIN);
        $detail = $db->where("fb_id = {$fb_id}")->find();
        $this->ajaxReturn($detail);
    }

    public function semdReply(){
        $content = $_POST['content'];
        $fb_id = $_POST['fb_id'];
        $user_id = $_POST['user_id'];
        $res = Push::sendCommonNotice($user_id,'客服回复通知','您有一条客服回复，请查看。',$content,'回复','#09cc9f');

        $db = M("t_client_feedback",null,DB_ADMIN);
        $data = [
            'deal_state'=>1,
            'reply_content'   => $content,
            'reply_dt'    => date('Y-m-d H:i:s'),
            'reply_user'=>$this->getLoginParam("admin_name")
        ];
        $result = $db->where("fb_id=".$fb_id)->save($data);
        $this->ajaxReturn($res);
    }

}