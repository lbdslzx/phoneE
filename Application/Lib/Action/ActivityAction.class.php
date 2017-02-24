<?php
/**
 * @example      活动控制器
 * @file         ActivityAction.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2016/1/25 0025
 * @time         14:03
 */
class ActivityAction extends CommonAction{
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
     * 活动列表
     */
    public function activity(){
        $table_title = [
            'activity_id'           => '活动ID',
            'activity_title'        => '活动标题',
            'activity_img'          => '活动图片',
            'client_type'           => '活动类型',
            'push_state'            => '推送状态',
            'click_num'             => '点击量',
            'start_dt'              => '开始时间',
            'end_dt'                => '结束时间',
            'order'                 => '操作指示',
        ];
        $this->assign("table_title",$table_title);//列表页文章标题
        $this->display();
    }

    /**
     * 异步拉取活动列表
     */
    public function getActivityList(){
        parent::getList();
        $db = M("t_push_activity_info",null,DB_WEB);

        $where = "1 = 1";
        if(isset($this->push_state) && $this->push_state != ""){
            $where .= " AND push_state IN ({$this->push_state})";
        }

        $activity_state = intval(@$this->activity_state);
        $now = strtotime('now');
        switch($activity_state){
            case 1:
                $where .= " AND UNIX_TIMESTAMP(start_dt) > {$now}";
                break;
            case 2:
                $where .= " AND UNIX_TIMESTAMP(start_dt) < {$now} AND UNIX_TIMESTAMP(end_dt) > {$now}";
                break;
            case 3:
                $where .= " AND UNIX_TIMESTAMP(end_dt) < {$now}";
                break;
            default:
                break;
        }

        if(isset($this->activity_title) && trim($this->activity_title) != ''){
            $activity_title = trim($this->activity_title);
            $where .= " AND activity_title LIKE '%{$activity_title}%'";
        }
        switch ($this->order_by){
            case 1:
                $orderBy = "start_dt DESC,activity_id DESC";
                break;
            case 2:
                $orderBy = "click_num DESC,start_dt DESC,activity_id DESC";
                break;
            default:
                $orderBy = "update_dt DESC,activity_id DESC";
                break;
        }

        $totalRecords = $db->where($where)->count();
        $result = $db->where($where)->order($orderBy)
            ->page($this->page,$this->rows)
            ->select();
        $list = [];
        foreach($result as $key => $value){
            $order = "";
            if(in_array($value['push_state'],[1,2,3]) &&strtotime($value['end_dt']) >= strtotime('now') && strtotime($value['start_dt']) <= strtotime('now')){
                $order .= "<a href='javascript:void(0);' onclick='goPush({$value['activity_id']},{$value['client_type']})'>推送</a>&nbsp;";
            }
            $order .= "<a href='activityEdit?id={$value['activity_id']}'>编辑</a>&nbsp;<a href='javascript:void(0);' onclick='activityDel({$value['activity_id']})'>删除</a>";
            $list[] = [
                'activity_id'    => $value['activity_id'],
                'activity_title' => $value['activity_title'],
                'activity_img'   => "<img src='{$value['activity_img']}' width='50px' onclick='view_pic(\"{$value['activity_img']}\");' />",
                'client_type'    => $value['client_type']==1 ? '贵州版' : ($value['client_type'] == 2 ? '全国版' : '贵州版+全国版'),
                'push_state'    => $value['push_state'] == 1 ? '未推送' : ($value['push_state'] == 2 ? '推送中' : ($value['push_state'] == 3 ? '已推送' : '不推送')),
                'start_dt'      => $value['start_dt'],
                'click_num'     => $value['click_num'],
                'end_dt'        => $value['end_dt'],
                'order'         => $order
            ];
        }
        $table_data["total"]    = $totalRecords;
        $table_data["rows"]     = $list;
        $this->ajaxReturn($table_data);
    }

    /**
     * 活动编辑
     */
    public function activityEdit(){
        parent::getList();
        $db = M("t_push_activity_info",null,DB_WEB);
        if(IS_POST){
            $input = $_POST;
            $extend = [
                'action' => '',
                'params' => []
            ];
            $extend = json_encode($extend);
            $activityId = $input['activity_id'] ? $input['activity_id'] : $db->max('activity_id')+1;
            $data = [
                'activity_id'       => $activityId,
                'activity_title'    => $input['activity_title'],
                'activity_img'      => $input['activity_img'],
                'activity_abstract' => $input['activity_abstract'],
                'activity_web_url'  => $input['activity_web_url'],
                'activity_share_url'=> $input['activity_share_url'],
                'push_state'        => $input['push'] == 0 ? 4 : (in_array($input['push_state'],[4,5]) ? 1 : $input['push_state']) ,
                'extend'            => $extend,
                'start_dt'          => $input['start_dt'],
                'end_dt'            => $input['end_dt'],
                'create_dt'         => date('Y-m-d H:i:s'),
                'update_dt'         => date('Y-m-d H:i:s'),
                'client_type'       => $input['client_type']
            ];
            if($input['activity_id']){
                flushMemcache();
                $result = $db->save($data);
            }else{
                $result = $db->add($data);
            }
            ///推送
            if(strtotime($data['end_dt']) >= strtotime('now') && strtotime($data['start_dt']) <= strtotime('now') && $input['push']){
                $this->_activityPush($activityId,$input['activity_title'],$input['activity_abstract'],$input['client_type']);
            }
            $this->ajaxReturn($result);
        }

        $detail = $this->id ? $db->where("activity_id = {$this->id}")->find() : array('push_state'=>1,'client_type'=>1);
        $detail['push_state'] = $detail['push_state'] == 5 ? 4 :$detail['push_state'];
        $this->assign('detail',$detail);
        ///图片上传
        $hfs = new HfsModel(HfsModel::OP_TYPE_INFORMATION_IMG);
        $hfs->addParm("file_name", getMillisecond().".jpg");
        $upload_addr = $hfs->getJson();
        $down_addr = $hfs->getDownUrl();
        $upload_addr = "../Information/uploadPic?json=".$upload_addr;
        $this->assign("upload_addr",$upload_addr);
        $this->assign("down_addr",$down_addr);
        $this->display();
    }
    public function activityPub(){
        parent::getList();
        $db = M("t_push_activity_info",null,DB_WEB);
        $data = [
            'pub_state'     => 1,
            'activity_id'   => $this->id,
            'push_state'    => $this->push_state == 4 ? 4 : 2,
        ];
        $result = $db->save($data);
        if($result){
            $detail = $db->where("activity_id = {$this->id}")->find();
            $this->_activityPush($this->id,$detail['activity_title'],$detail['activity_abstract'],$detail['client_type']);
        }
        return $this->ajaxReturn($result);
    }
    /**
     * 活动推送
     * @return bool
     */
    public function activityPush(){
        parent::getList();
        $db = M("t_push_activity_info",null,DB_WEB);
        $detail = $db->where("activity_id = {$this->id}")->find();
        if(is_array($detail)) {
            $result = $this->_activityPush($this->id,$detail['activity_title'],$detail['activity_abstract'],$detail['client_type']);
        }else{
            $result = -1;
        }
        $this->ajaxReturn($result);
    }

    /**
     * 活动推送
     * @param $activityId
     * @param $title
     * @param $desc
     * @param $clientType
     * @return bool
     */
    private function _activityPush($activityId,$title,$desc,$clientType=1){
        $db = M("t_push_activity_info",null,DB_WEB);
        $data = array(
            "activity_id"   => $activityId,
            'push_state'    => 2,
        );
        $db->save($data);
        $result = Push::messagePush(302,$activityId,$title,$desc,$clientType);
        if($result){
            return true;
        }else{
            $data = array(
                "activity_id"   => $activityId,
                'push_state'    => 1
            );
            $db->save($data);
            return false;
        }
    }

    /**
     * 删除活动
     */
    public function activityDel(){
        parent::getList();
        $db = M("t_push_activity_info",null,DB_WEB);
        $result = $db->delete($this->id);
        flushMemcache();
        $this->ajaxReturn($result);
    }
}