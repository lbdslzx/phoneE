<?php
/**
 * @example     首页Banner管理
 * @file         BannerAction.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2015/10/20 0020
 * @dime         15:58
 */
class BannerAction extends CommonAction{
    public function top(){
        $table_title = [
            'id'                        => '编号',
            'activity_picture_name'     => '图片',
            'activity_url'              => '跳转地址',
            'insert_dt'                 => '添加时间',
            'order_id'                  => '排序',
            'action'                    => '操作'
        ];
        $this->assign("table_title",$table_title);//列表页文章标题
        $this->display();
    }
    public function getTopList(){
        parent::getList();
        $db = M("t_sys_activity_cfg",null,DB_SYS);
        $totalRecords = $db->count();
        $list = $db->order('order_id DESC,insert_dt DESC,id ASC')
            ->page($this->page,$this->rows)
            ->select();
        foreach($list as $key => $value){
            $value['activity_picture_name'] = "<img src='{$value['activity_picture_name']}' width='50px' onclick='view_pic(\"{$value['activity_picture_name']}\");' />";
            $value['order_id'] = "<input rel='{$value['id']}'onkeyup=\"this.value=this.value.replace(/\D/g,'')\"  onafterpaste=\"this.value=this.value.replace(/\D/g,'')\"  size=\"3\" maxlength='3'  type='text' value='{$value['order_id']}' name='order_id' />";
            $value['action']  = "<a href='topEdit?id={$value['id']}'>编辑</a>&nbsp;|&nbsp;<a href='javascript:void(0);' onclick='top_del({$value['id']})'>删除</a>";
            $list[$key] = $value;
        }
        $table_data["total"] = $totalRecords;
        $table_data["rows"] = $list;
        $this->ajaxReturn($table_data);
    }
    public function topEdit(){
        parent::getList();
        $db = M("t_sys_activity_cfg",null,DB_SYS);
        if(IS_POST){
            $data = $_POST;
            if($data['id']){
                $result = $db->save($data);
            }else{
                $data['insert_dt'] = date('Y-m-d H:i:s');
                $result = $db->add($data);
            }
            $this->log_trace(print_r($data,true));
            flushMemcache();
            $this->ajaxReturn($result);
        }
        $detail = $this->id ? $db->where("id = {$this->id}")->find() : array('order_id'=>0);
        $title = $this->id ? "编辑" : "添加";
        $this->assign('detail',$detail);
        $this->assign('title',$title);
        //图片上传
        $hfs = new HfsModel(HfsModel::OP_TYPE_INFORMATION_IMG);
        $hfs->addParm("file_name", getMillisecond().".jpg");
        $upload_addr = $hfs->getJson();
        $down_addr = $hfs->getDownUrl();
        $upload_addr = "../Information/uploadPic?json=".$upload_addr;
        $this->assign("upload_addr",$upload_addr);
        $this->assign("down_addr",$down_addr);
        $this->display();
    }
    public function topDel(){
        $db = M("t_sys_activity_cfg",null,DB_SYS);
        $id = $_POST['id'];
        $result = $db->delete($id);
        flushMemcache();
        $this->ajaxReturn($result);
    }
    public function topSaveOrder(){
        $db = M("t_sys_activity_cfg",null,DB_SYS);
        $data = $_POST['data'];
        $result = 1;
        foreach($data as $key => $value){
            $result += $db->save($value);
        }
        flushMemcache();
        $this->ajaxReturn($result);
    }
    public function center(){
        $table_title = [
            'module_id'                 => '编号',
            'module_name'               => '标题',
            //'module_url'                => '跳转地址',
            'pic_name'                  => '图片地址',
            'pic_url'                   => '跳转地址',
            'doc_id'                    => '栏目医生',
            'cfg_state'                 => '状态',
            'insert_dt'                 => '发布时间',
            'sort'                 => '排序',
            'action'                    => '操作'
        ];
        $this->assign("table_title",$table_title);//列表页文章标题
        $this->display();
    }
    public function getCenterList(){
        $db = M('t_home_doctor_cfg',null,DB_QUERY);
        $totalRecords = $db->count();
        $list = $db->where(array("is_delete"=>0))->order('order_id DESC,cfg_state DESC,insert_dt DESC,module_id ASC')
            ->page($this->page,$this->rows)
            ->select();
        foreach($list as $key => $value){
            $value['pic_name'] = "<img src='{$value['pic_name']}' width='50px' onclick='view_pic(\"{$value['pic_name']}\");' />";
            $value['action'] = "<a href='centerEdit?id={$value['module_id']}'>编辑</a>";
            if($value['cfg_state'] == 0){
                $value['action'] .= "&nbsp;|&nbsp;<a href='javascript:void(0);' onclick='center_change_state({$value['module_id']},1)'>启用</a>";
            }elseif($value['cfg_state'] == 1){
                $value['action'] .= "&nbsp;|&nbsp;<a href='javascript:void(0);' onclick='center_change_state({$value['module_id']},0)'>停用</a>";
            }

            $value['cfg_state'] = $value['cfg_state'] == 1 ? "已启用" : "已停用";
            $sid="sort_".$value['module_id'];
            $pid=$value['module_id'];
            $value['action'] .= "&nbsp;|&nbsp;<a href='javascript:void(0);' onclick=delBanner('".$pid."') >删除</a>";
            $value["sort"]="<input id='".$sid."' value='".$value['order_id']."' style='width:60px;height:20px;' onBlur=altSort('".$sid."','".$pid."') />";
            $list[$key] = $value;
        }
        $table_data["total"] = $totalRecords;
        $table_data["rows"] = $list;
        $this->ajaxReturn($table_data);
    }
    public function centerEdit(){
        $db = M('t_home_doctor_cfg',null,DB_QUERY);
        if(IS_POST){
            $data = $_POST;
            if($data['module_id']){
                $module_id = $data['module_id'];
                unset($data['module_id']);
                $result = $db->where("module_id = {$module_id}")->save($data);
                //$result = $db->save($data);
            }else{
                $module_id = $db->max('module_id');
                $data['cfg_state'] = $module_id > 0 ? 0 : 1;
                $data['insert_dt'] = date('Y-m-d H:i:s');
                $data['module_id'] = $module_id+1;
                $result = $db->add($data);
            }
            $this->log_trace(print_r($data,true));
            flushMemcache();
            $this->ajaxReturn($result);
        }
        parent::getList();
        $detail = $this->id ? $db->where("module_id = {$this->id}")->find() : array();
        $title = $this->id ? "编辑" : "添加";
        $this->assign('detail',$detail);
        $this->assign('title',$title);

        //图片上传
        $hfs = new HfsModel(HfsModel::OP_TYPE_INFORMATION_IMG);
        $hfs->addParm("file_name", getMillisecond().".jpg");
        $upload_addr = $hfs->getJson();
        $down_addr = $hfs->getDownUrl();
        $upload_addr = "../Information/uploadPic?json=".$upload_addr;
        $this->assign("upload_addr",$upload_addr);
        $this->assign("down_addr",$down_addr);
        $this->display();
    }
    public function centerChangState(){
        $data = $_POST;
        $db = M('t_home_doctor_cfg',null,DB_QUERY);
        $module_id = $data['module_id'];
        $result = $db->where("module_id = $module_id")->save($data);
        flushMemcache();
        $this->ajaxReturn($result);
    }
/*    public function getDoctorList(){
        parent::getList();
        $apiUrl = formateURIAddr(C("hlwyy_cfg.api_url"));
        $appId = C("hlwyy_cfg.app_id");
        $appKey = C("hlwyy_cfg.app_key");
        $page = $this->page ? $this->page : 1;
        $page_size = $this->rows ? $this->rows : 15;
        $url = $apiUrl."doctors?app_id=".$appId."&app_key=".$appKey."&page=".$page."&page_size=".$page_size;
        $result = file_get_contents($url);
        $result = json_decode($result,true);
        $list = [];
        if(isset($result['code']) && $result['code'] == 0){
            foreach($result['list'] as $key => $value){
                $list[] = [
                    'id'            => "<input type='checkbox' class='select_doc_id' id='ch_doc_id_{$value['doc_id']}' name='id[]' value='{$value['doc_id']}'>",
                    'doc_id'        => $value['doc_id'],
                    'doc_name'      => $value['doc_name'],
                    'hospital'      => $value['hospital'],
                    'department'    => $value['department']
                ];
            }
        }
        $table_data["total"] = intval(@$result['total']);
        $table_data["rows"] = $list;
        $this->ajaxReturn($table_data);
    }*/

    public function getDoctorList(){
        $db = M('t_doctor_info',null,DB_SYS_HLWYY);

        $page=isset($_POST['page'])?$_POST['page']:1;
        $pageSize=isset($_POST['row'])?$_POST['row']:15;
        $start=$page*$pageSize-$pageSize;

        $where['online_state']=3;
        $where['province_id']=1;//只拉取贵州省的医生
        $count=$db->where($where)->count();
        $res=$db->where($where)->field("doc_id,doc_name,hospital,department")->limit($start,$pageSize)->select();
        $list = [];
        foreach($res as $key => $value){
                $list[] = [
                    'id'            => "<input type='checkbox' class='select_doc_id' id='ch_doc_id_{$value['doc_id']}' name='id[]' value='{$value['doc_id']}'>",
                    'doc_id'        => $value['doc_id'],
                    'doc_name'      => $value['doc_name'],
                    'hospital'      => $value['hospital'],
                    'department'    => $value['department']
                ];
        }
        $table_data["total"] = $count;
        $table_data["rows"] = $list;
        $this->ajaxReturn($table_data);
    }

    //更新排序
    public function upSort(){
        if(isset($_POST['module_id'])){
            $where['module_id']=$_POST['module_id'];
            $data['order_id']=$_POST['order_id'];
            $table = M('t_home_doctor_cfg',null,DB_QUERY);
            if($table->where($where)->save($data)){
                flushMemcache();
                echo  "y";
            }else{
                echo "n";
            }
        }else{
            return "n";
        }

    }
    //更新排序
    public function delBanner(){
        if(isset($_POST['module_id'])){
            $where['module_id']=$_POST['module_id'];
            $data['is_delete']=1;
            $table = M('t_home_doctor_cfg',null,DB_QUERY);
            if($table->where($where)->save($data)){
                flushMemcache();
                echo  "y";
            }else{
                echo "n";
            }
        }else{
            return "n";
        }

    }
}