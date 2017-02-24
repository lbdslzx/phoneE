<?php
/**
 * @example      微信控制器
 * @file         WeixinAction.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2016/1/25 0025
 * @time         14:03
 */

class WeixinAction extends CommonAction{
    public function index(){
        parent::getList();
        $db = M("t_weixin_hd",null,DB_SYS);
        if(IS_POST){
            $data = ['id'=>1,'wx_need_token'=>$this->wx_need_token];
            $result = $db->save($data);
            $this->ajaxReturn($result);
        }
        $detail = $db->where("id = 1")->find();
        $this->assign('detail',$detail);
        $this->display();
    }
}