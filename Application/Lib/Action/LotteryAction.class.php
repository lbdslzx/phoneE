<?php
/**
 * @example      摇奖活动
 * @file         LotteryAction.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2016-04-06
 * @time         10:33
 * @copyright © 2016, Longmaster Corporation. All right reserved.
 */
class LotteryAction extends CommonAction{
    ///摇奖设置
    public function setting(){
        $db = M("t_user_recommend_participate", null, DB_ACTIVITY);
        $participateSum = $db->where("CHAR_LENGTH(be_user_id) = 7 AND user_id <> 1")->count();
        $tableTitle = [
            "lottery_code"          => "开奖期号",
            "lottery_explain"       => "规则说明",
            "prize_name"            => "奖品名称",
            "prize_img"             => "奖品图片",
            "cumulative_standard"   => "开奖累计标准",
            "prize_num"             => "拟中奖数量",
            //"start_dt"              => "起始时间",
            //"end_dt"                => "截止时间",
            "end_dt"                => "开奖时间",
            "state"                 => "状态",
            "insert_dt"             => "配置时间",
            "opera"                 => "操作"
        ];
        $this->assign("participate_sum", $participateSum);
        $this->assign("table_title", $tableTitle);
        $this->display("lottery_setting_list");
    }
    ///摇奖设置列表
    public function getLotterySettingList(){
        parent::getList();
        $db = M("t_lottery_set", null, DB_ACTIVITY);
        $fields = [
            "lottery_id",
            "lottery_code",
            "lottery_explain",
            "prize_name",
            "prize_img",
            "cumulative_standard",
            "prize_num",
            "start_dt",
            "end_dt",
            "CASE state WHEN 0 THEN '待开奖' WHEN 1 THEN '开奖中' WHEN 2 THEN '已开奖' WHEN 3 THEN '取消' END  AS 'state'",
            "insert_dt"
        ];
        $where = " lottery_id >0 ";
        $input = $_POST;
        if(isset($input['bt']) && !empty($input['bt']) && isset($input['et']) && !empty($input['et'])){
            $where .= " AND DATE_FORMAT(end_dt,'%Y-%m-%d') BETWEEN '{$input['bt']}' AND '{$input['et']}'";
        }
        if (isset($input['lottery_code']) && !empty($input['lottery_code'])) {
            $where .= " AND lottery_code LIKE '%{$this->lottery_code}%' ";
        }
        //总条数
        $totalRecords = $db->where($where)->count();
        $data = $db
            ->where($where)
            ->order("Cumulative_standard ASC")
            ->page($this->page, $this->rows)
            ->field($fields)
            ->select();
        $data = is_array($data) ? $data : [];
        $list = $data;
        $table_data["total"] = $totalRecords;
        $table_data["rows"] = $list;
        $this->ajaxReturn($table_data);
    }
    ///摇奖设置编辑
    public function settingEdit(){
        parent::getList();
        $db = M("t_lottery_set", null, DB_ACTIVITY);
        if(IS_POST){
            $input = $_POST;
            $data = [
                // 'lottery_order'       => $input['lottery_order'],
                'lottery_code'          => $input['lottery_code'],
                'lottery_explain'       => $input['lottery_explain'],
                'cumulative_standard'   => $input['cumulative_standard'],
                'prize_name'            => $input['prize_name'],
                'prize_img'             => $input['prize_img'],
                'prize_num'             => $input['prize_num'],
                // 'state'               => 0,
                'insert_dt' => date('Y-m-d H:i:s')
            ];
            if($input['lottery_id']){
                $data['lottery_id'] = $input['lottery_id'];
                $result = $db->save($data);
            }else{
                $result = $db->add($data);
            }
            $this->ajaxReturn($result);
        }
        $lotteryId = $this->lottery_id;
        $detail = $lotteryId ? $db->where("lottery_id = {$lotteryId}")->find() : [];
        $this->assign('detail', $detail);       
        ///图片上传
        $hfs = new HfsModel(HfsModel::OP_TYPE_INFORMATION_IMG);
        $hfs->addParm("file_name", getMillisecond() . ".jpg");
        $upload_addr = $hfs->getJson();
        $down_addr = $hfs->getDownUrl();
        $upload_addr = "../Information/uploadPic?json=" . $upload_addr;
        $this->assign("upload_addr", $upload_addr);
        $this->assign("down_addr", $down_addr);
        $this->display("lottery_setting_edit");
    }
    ///删除摇奖设置
    public function settingDel(){
        parent::getList();
        $lotteryId = $this->lottery_id;
        $table = M("t_lottery_set", null, "db_activity");
        $result = $table->delete($lotteryId);
        $this->ajaxReturn($result);
    }
    public function getRepeatCode(){
        parent::getList();
        $table = M("t_lottery_set", null, "db_activity");
        $repeatCode = $table->where("lottery_code = '{$this->lottery_code}' AND lottery_id <> '{$this->lottery_id}'")->count();
        if ($repeatCode == 0) {
            $arr = array("flag" => false, msg => "无数据重复");
        } else {
            $arr = array("flag" => true, msg => "数据重复");

        }
        $this->ajaxReturn($arr);
    }
    //推荐口令
    public function passwordQuery(){
        $this->display("password_query_list");
    }
    //推荐口令列表
    public function getPasswordList(){
        parent::getList();
        $db = M("t_user_recommend_passward", null, DB_ACTIVITY);
        $fields = [
            "seq_id",
            "user_id",
            "recommend_password",
            "insert_dt"
        ];
        $where = " seq_id >0 ";
        $input = $_POST;
        if(isset($input['bt']) && !empty($input['bt']) && isset($input['et']) && !empty($input['et'])){
            $where .= " AND DATE_FORMAT(insert_dt,'%Y-%m-%d') BETWEEN '{$input['bt']}' AND '{$input['et']}'";
        }
        if (isset($input['recommend_password']) && $input['recommend_password'] != '') {
            $where .= " and recommend_password like '%{$input['recommend_password']}%' ";
        }
        //总条数
        $totalRecords = $db->where($where)->count();
        $data = $db
            ->where($where)
            ->order(" insert_dt DESC,user_id ASC")
            ->page($this->page, $this->rows)
            ->field($fields)
            ->select();
        $data = is_array($data) ? $data : [];
        $table_data["total"] = $totalRecords;
        $table_data["rows"] = $data;
        $this->ajaxReturn($table_data);
    }
    ///删除推荐口令
    public function passwordDel(){
        parent::getList();
        $table = M("t_user_recommend_passward", null, "db_activity");
        $result = $table->delete($this->seq_id);
        $this->ajaxReturn($result);
    }
    ///活动参与查询
    public function partakeQuery(){
        $this->display("participate_query_list");
    }
    ///活动参与列表
    public function getPartakeList(){
        parent::getList();
        $input = $_POST;
        $db = M("t_user_recommend_participate", null, DB_ACTIVITY);
        $fields = array(
            "participate_id",
            "user_id",
            "be_user_id",
            "tourist_code",
            "recommend_password",
            "insert_dt"
        );
        //$where = "CHAR_LENGTH(be_user_id) = 7";
        $where = ["1=1"];
        if(isset($input['bt']) && !empty($input['bt']) && isset($input['et']) && !empty($input['et'])){
            $where[] = "DATE_FORMAT(insert_dt,'%Y-%m-%d') BETWEEN '{$input['bt']}' AND '{$input['et']}'";
        }
        if (isset($input['recommend_password']) && $input['recommend_password'] != '') {
            $where[] = "recommend_password like '%{$input['recommend_password']}%'";
        }
        if(!empty($input['user_type'])){
            if(in_array($input['user_type'],[1,2])){
                $where[] = "user_id = ".($input['user_type']-1);
            }else{
                $where[] = "user_id NOT IN (0,1)";
            }
        }
        if($input['be_user_type'] == 1){
            $where[] = "CHAR_LENGTH(be_user_id) = 7";
        }elseif ($input['be_user_type'] == 2){
            $where[] = "CHAR_LENGTH(be_user_id) = 9";
        }
        //总条数
        $totalRecords = $db->where($where)->count();
        $data = $db
            ->where($where)
            ->order(" insert_dt DESC,tourist_code ASC ")
            ->page($this->page, $this->rows)
            ->field($fields)
            ->select();
        $data = is_array($data) ? $data : [];
        $table_data["total"] = $totalRecords;
        $table_data["rows"] = $data;
        $this->ajaxReturn($table_data);
    }
    public function getUserInfo(){
        parent::getList();
        $table = M("t_user_phone",null,DB_QUERY);
        $phone = $table->field("phone_num")->where("user_id={$this->user_id}")->find();
        $phone = substr($phone['phone_num'],-11);
        $table = M("t_user_config",null,DB_UC);
        $userName = $table->field("user_name")->where("user_id = {$this->user_id}")->find();
        $data = array_merge($userName,["phone_num"=>$phone]);
        $this->ajaxReturn($data);
    }
    ///删除参与
    public function partakeDel(){
        parent::getList();
        $table = M("t_user_recommend_participate", null, "db_activity");
        $result = $table->delete($this->participate_id);
        $this->ajaxReturn($result);
    }
    ///抽奖码及中奖情况
    public function winningInformation(){
        $this->display("winning_information_list");
    }
    ///中奖列表
    public function getWinningList(){
        parent::getList();
        $input = $_POST;
        $db = M("t_user_draw_pool as d", null, DB_ACTIVITY);
        $fields = [
            "seq_id",
            "user_id",
            "draw_code",
            "participate_id",
            "d.lottery_id as lottery_id",
            "lottery_code",
            "prize_name",
            "prize_img",
            "end_dt"
        ];
        $where = "d.lottery_id  IS NOT NULL";
        if(isset($input['bt']) && !empty($input['bt']) && isset($input['et']) && !empty($input['et'])){
            $where .= " AND DATE_FORMAT(end_dt,'%Y-%m-%d') BETWEEN '{$input['bt']}' AND '{$input['et']}'";
        }
        if (isset($input['draw_code']) && $input['draw_code'] != '') {
            $where .= " and draw_code like '%{$input['draw_code']}%' ";
        }
        //总条数
        $totalRecords = $db->where($where)->count();
        $data = $db
            ->join("LEFT JOIN t_lottery_set l on d.lottery_id=l.lottery_id")
            ->where($where)
            ->order(" d.seq_id asc ")
            ->page($this->page, $this->rows)
            ->field($fields)
            ->select();
        $data = is_array($data) ? $data : [];
        $table_data["total"] = $totalRecords;
        $table_data["rows"] = $data;
        $this->ajaxReturn($table_data);
    }
    ///获奖信息详情
    public function winningInfo(){
        $db = M("t_user_draw_pool as d", null, DB_ACTIVITY);
        $fields = [
            "seq_id",
            "user_id",
            "draw_code",
            "participate_id",
            "d.lottery_id as lottery_id",
            "lottery_code",
            "prize_name",
            "prize_img",
            "end_dt"
        ];
        parent::getList();
        $data = $db->field($fields)->join("LEFT JOIN t_lottery_set l on d.lottery_id=l.lottery_id")->where(['seq_id'=>$this->seq_id])->find();
        $table = M("t_user_phone",null,DB_QUERY);
        $phone = $table->field("phone_num")->where("user_id={$this->user_id}")->find();
        $phone = substr($phone['phone_num'],-11);
        $table = M("t_user_config",null,DB_UC);
        $userName = $table->field("user_name")->where("user_id = {$this->user_id}")->find();
        $data = array_merge($data,$userName,["phone_num"=>$phone]);
        $this->assign("info", $data);
        $this->display("winning_information_info");
    }
    //删除获取信息
    public function winningDel(){
        parent::getList();
        $table = M("t_user_draw_pool", null, "db_activity");
        $result = $table->delete($this->seq_id);
        $this->ajaxReturn($result);
    }
    ///领奖信息
    public function awardInformation(){
        $this->display("award_information_list");
    }
    ///获取领奖列表
    public function getAwardList(){
        parent::getList();
        $db = M("t_user_get_prize a",null,DB_ACTIVITY);
        $input = $_POST;
        $where = "1=1";
        if(isset($input['bt']) && !empty($input['bt']) && isset($input['et']) && !empty($input['et'])){
            $where .= " AND DATE_FORMAT(a.get_dt,'%Y-%m-%d') BETWEEN '{$input['bt']}' AND '{$input['et']}'";
        }
        if (isset($input['lottery_code']) && $input['lottery_code'] != '') {
            $where .= " and b.lottery_code like '%{$input['lottery_code']}%' ";
        }
        $fields = [
            'a.user_id AS user_id',
            'a.draw_code AS draw_code',
            'a.lottery_id AS lottery_id',
            'b.lottery_code AS lottery_code',
            'a.prize_intro AS prize_intro',
            'b.prize_name AS prize_name',
            'a.get_dt AS get_dt'
        ];
        $join = "t_lottery_set AS b ON b.lottery_id = a.lottery_id";
        $total = $db->join($join)->where($where)->count();
        $list = $db->join($join)
            ->where($where)
            ->field($fields)
            ->order("a.get_dt DESC,a.draw_code ASC")
            ->page($this->page, $this->rows)
            ->select();
        $table_data["total"] = $total;
        $table_data["rows"] = is_array($list) ? $list : [];
        $this->ajaxReturn($table_data);
    }
    public function awardEdit(){
        parent::getList();
        $db = M("t_user_get_prize",null,DB_ACTIVITY);
        $drawCode = @$_GET['draw_code'];
        if(IS_POST){
            $input = $_POST;
            $data = [
                'user_id'       => $input['user_id'],
                'lottery_id'    => $input['lottery_id'],
                'draw_code'     => $input['draw_code'],
                'prize_intro'   => $input['prize_intro'],
                'get_dt'        => date('Y-m-d H:i:s')
            ];
            $result = $drawCode ? $db->where("draw_code = '{$drawCode}'")->save($data) : $db->add($data);
            $this->ajaxReturn($result);
        }
        $drawCode = @$_GET['draw_code'];
        $detail = $drawCode ? $db->where("draw_code = '{$drawCode}'")->find() : [];
        $this->assign('detail',$detail);
        $fields = [
            'a.user_id AS user_id',
            'a.draw_code AS draw_code',
            'a.lottery_id AS lottery_id',
            'b.lottery_code AS lottery_code',
            'b.prize_name AS prize_name',
        ];
        $join = "t_lottery_set AS b ON b.lottery_id = a.lottery_id";
        $where = "a.draw_code NOT IN(SELECT draw_code FROM t_user_get_prize) AND a.lottery_id IS NOT NULL AND CHAR_LENGTH(a.user_id)=7";
        if($drawCode){
            $where .= " OR a.draw_code = '{$_GET['draw_code']}'";
        }
        $db = M("t_user_draw_pool a",null,DB_ACTIVITY);
        $data = $db->where($where)->field($fields)->join($join)->select();
        $data = is_array($data) ? $data : [];
        $awardJson = json_encode($data);
        $this->assign("award_data",$awardJson);
        $this->display("award_information_edit");
    }
    public function awardDel(){
        parent::getList();
        $db = M("t_user_get_prize",null,DB_ACTIVITY);
        $result = $db->where("draw_code = '{$this->draw_code}'")->delete();
        $this->ajaxReturn($result);
    }
}