<?php
/**
 * @example      知识库控制器
 * @file         InformationAction.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2015/10/19 0019
 * @dime         10:16
 */
class InformationAction extends CommonAction{
    private $_db_name = DB_QUERY;//所选数据库
    private $_db_table = 't_app_health_info_from_39';//所用数据表

    public function __construct(){
        parent::__construct();
        import("@.CustomLib.Push");
        import("@.CustomLib.ArrayToXML");
        import("@.CustomLib.Common");
        import("@.CustomLib.CryptAES");
    }

    /**
     * 知识库首页列表页
     */
    public function index(){
        $table_title = [
            'article_title' => '文章标题',
            'title_pic'     => '标题图片',
            'forum_name_lm' => '所属栏目',
            'author'        => '文章来源',
            'pub_date'      => '发布时间',
            'actually_read_count'    => '访问量',
            'action'        => '操作提示'
        ];
        $this->assign("table_title",$table_title);//列表页文章标题
        //获取分类栏目
        $this->assign('forum_list',$this->_getForumList());//分页栏目
        //获取文章来源
       // $this->assign('author_list',$this->_getAuthorList());//文章来源
        $this->display();
    }

    /**
     * 栏目分类
     * @return mixed
     */
    private function _getForumList(){
        $db = M($this->_db_table,null,$this->_db_name);
        $forumList = $db->where('forum_id_lm <> 0')
            ->distinct('forum_id_lm')
            ->field('forum_id_lm,forum_name_lm')
            ->order('forum_id_lm asc')
            ->select();
        return $forumList;
    }

    /**
     * 文章来源
     * @return mixed
     */
    private function _getAuthorList(){
        $db = M($this->_db_table,null,$this->_db_name);
        $forumList = $db->where('forum_id_lm <> 0')
            ->distinct('forum_id_lm')
            ->field('author')
            ->select();
        return $forumList;
    }

    /**
     * 异步获取文章列表
     */
    public function getList(){
        parent::getList();
        $db = M($this->_db_table,null,$this->_db_name);
        $where = "forum_id_lm <> 0" ;
        if($this->forum_id > 0){
            $where = "forum_id_lm = {$this->forum_id}";
        }
        $article_title = trim($this->article_title);
        if($article_title){//标题
            $where .= " AND article_title LIKE '%{$article_title}%'";
        }
        if($this->author){//来源
            $where .= " AND author = '{$this->author}'";
        }else{
            $where .= " AND author IN('39健康网','贵健康')";
        }
        if($this->is_good){
            $is_good = $this->is_good -1;
            $where .= " AND is_good = {$is_good}";
        }
        //总条数
        $totalRecords = $db->where($where)->count();
        //列表页内容
        $list = [];
        $result = $db->where($where)
            ->field('article_title,title_pic,forum_id_lm,forum_name_lm,author,pub_date,article_id,is_good,lm_top,actually_read_count')
            ->order("lm_top DESC,pub_date DESC,article_id ASC")
            ->page($this->page,$this->rows)
            ->select();
        foreach($result as $k => $v){
            $value = [
                'article_title' => $v['article_title'],
                'title_pic'     => $v['title_pic'] ? "<img alt='{$v['article_title']}' style='height: 24px;' title='{$v['article_title']}' src='{$v['title_pic']}' width='50px' onclick='view_pic(\"{$v['title_pic']}\");' />" : "无",
                'forum_name_lm' => $v['forum_name_lm'],
                'author'        => $v['author'] ? $v['author'] : '贵健康',
                'pub_date'      => $v['pub_date'],
                'read_count'    => $v['read_count'],
                'actually_read_count'=> $v['actually_read_count'],
                'lm_top'        => "<input rel='{$v['article_id']}'onkeyup=\"this.value=this.value.replace(/\D/g,'')\"  onafterpaste=\"this.value=this.value.replace(/\D/g,'')\"  size=\"10\" maxlength='10'  type='text' value='{$v['lm_top']}' name='lm_top' />",
                'action'        => "",
            ];
            $value['action'] = "<a href='javascript:void(0);' onclick='go_send(\"{$v['article_id']}\")'>推送</a>&nbsp;<a href='javascript:void(0);' onclick='go_top(\"{$v['article_id']}\",1)'>置顶</a>&nbsp;<a href='javascript:void(0);' onclick='go_top(\"{$v['article_id']}\",2)'>取消置顶</a>";
            $value['action'] .= $v['is_good'] ? "&nbsp;<a href='javascript:void(0);' onclick='set_essence(\"{$v['article_id']}\",\"2\")'>取消精选</a>" : "&nbsp;<a href='javascript:void(0);' onclick='set_essence(\"{$v['article_id']}\",\"1\")'>设为精选</a>";
            //$value['action'] .= $value['author'] == '贵健康' ? "&nbsp;<a href='edit?article_id={$v['article_id']}'>编辑</a>&nbsp;<a onclick='article_delete(\"{$v['article_id']}\")' href='javascript:void(0);'>删除</a>" : "";
            $value['action'] .= "&nbsp;<a href='edit?article_id={$v['article_id']}'>编辑</a>&nbsp;<a onclick='article_delete(\"{$v['article_id']}\")' href='javascript:void(0);'>删除</a>" ;
            $list[] = $value;
        }
        $table_data["total"] = $totalRecords;
        $table_data["rows"] = $list;
        $this->ajaxReturn($table_data);
    }

    /**
     * 编辑文章
     */
    public function edit(){
        $forumList = $this->_getForumList();
        $db = M($this->_db_table,null,$this->_db_name);
        $max = $db->max('lm_top');
        if(IS_POST){
            $data = $_POST;
            $send = $data['send'];
            unset($data['send']);
            foreach($forumList as $key => $value){
                if($value['forum_id_lm'] == $data['forum_id_lm']){
                    $data['forum_name_lm'] = $value['forum_name_lm'];
                    break;
                }
            }
            if($data['lm_top']){
                $data['lm_top'] = $max+1;
                $data['pub_date'] = date('Y-m-d H:i:s');
            }else{
                unset($data['lm_top']);
            }
            if(!$data['article_id']){//添加
                $maxId                      = $db->max('article_id');
                $article_id                 = $maxId >= 1000000000 ? $maxId+1 : 1000000000;
                $data['article_id']         = $article_id;
                $data['author']             = "贵健康";
                $date                       = date('Y-m-d H:i:s');
                $data['pub_date']           = $date;
                $data['insert_dt']          = $date;
                $data['forum_id_39']        = 0;
                $data['forum_name_39']      = '';
                $data['39_link']            = $data["39_link"];
                $data['title_pic_height']   = 0;
                $data['title_pic_width']    = 0;
                $data['read_count']         = rand(300,1000);
                $result = $db->add($data);
            }else{//修改
                $result = $db->save($data);
            }
            if($send){
                $this->_push($data['article_id'],$data['article_title'],$data['article_content'],$data['is_custom_link'],$data['39_link']);
            }
            $this->log_trace(print_r($data,true));
            flushMemcache();
            $this->ajaxReturn($result);
        }
        parent::getList();
        $article = $this->article_id ? $db->where("article_id = '{$this->article_id}'")->find() : array();
        $lm_top = (isset($article['lm_top']) && $max == $article['lm_top'] && $max !=0) ? 1 : 0;
        $this->assign('lm_top',$lm_top);//文章信息
        $title = $this->article_id ? "编辑" : "添加";
        $this->assign('article',$article);//文章信息
        $this->assign('forum_list',$forumList);//栏目详细
        $this->assign('title',$title);
        //图片上传
        $hfs = new HfsModel(HfsModel::OP_TYPE_INFORMATION_IMG);
        $hfs->addParm("file_name", getMillisecond().".jpg");
        $upload_addr = $hfs->getJson();
        $down_addr = $hfs->getDownUrl();
        $upload_addr = __URL__."/uploadPic?json=".$upload_addr;
        $this->assign("upload_addr",$upload_addr);
        $this->assign("down_addr",$down_addr);
        $this->display();
    }
    public function articleSend(){
        parent::getList();
        $db = M($this->_db_table,null,$this->_db_name);
        $article = $db->where("article_id = '{$this->article_id}'")->find();
        $result = $this->_push($this->article_id,$article['article_title'],$article['article_content'],$article['is_custom_link'],$article['39_link']);
        $this->ajaxReturn($result);
    }
    /**
     * 推送
     * @param $content
     * @param $title
     * @param $abstract
     * @return bool
     */
    private function _push($content,$title,$abstract,$isCustomLink,$link){
        if($isCustomLink) $abstract = file_get_contents($link);
        $abstract = strip_tags($abstract);
        $qian = array(" ", "　", "\t", "\n", "\r", "&nbsp;");
        $hou = array("", "", "", "", "", "");
        $abstract = str_replace($qian, $hou, $abstract);
        $abstract = mb_substr($abstract, 0, 100, 'utf-8');
        preg_match_all('/[\x{4e00}-\x{9fa5}0-9，.。；：“”‘’！？]+/u',$abstract,$chinese);
        $chinese = is_array($chinese) ? $chinese : [];
        $arr = [];
        foreach ($chinese as $value){
            $arr[] = $value[0];
        }
        $abstract = implode("",$arr);
        return Push::messagePush(201,$content,$title,$abstract);
    }
    public function uploadPic(){
        import("@.CustomLib.FileClass");
        $stream = file_get_contents($_FILES["file"]["tmp_name"]);
        $json = $_GET["json"];
        $json = $this->jsonParse($json);
        $file_name = $json["file_name"];
        $addr = C("information_image");
        $addr = formateURIAddr($addr);
        $addr .= $file_name;
        $this->log_trace("addr:".print_r($addr,true));
        $ret = FileClass::streamUpload($stream, $addr);
        $data = array(
            "file_name"		=>$file_name,
            "code"			=>$ret,
        );
        header('Content-Type: text/html'); //纯文本格式
        echo json_encode($data);
// 		$this->ajaxReturn($data,"json");
    }

    public function goTop(){
        parent::getList();
        $db = M($this->_db_table,null,$this->_db_name);
        $max = $db->max('lm_top');
        if($this->type == 1) {
            $data = [
                'article_id' => $this->article_id,
                'lm_top' => $max + 1,
                'pub_date' => date('Y-m-d H:i:s'),
            ];
        }else{
            $data = [
                'article_id' => $this->article_id,
                'lm_top' => 0,
            ];
        }
        $result = $db->save($data);
        if($result){
            flushMemcache();
            echo 0;
        }else{
            echo -1;
        }
    }
    public function saveOrder(){
        $db = M($this->_db_table,null,$this->_db_name);
        $data = $_POST['data'];
        $result = 1;
        $min = 0;
        foreach($data as $key => $value){
            $min = $min ? ($value['lm_top'] < $min ? $value['lm_top'] : $min ) : $value['lm_top'];
        }
        foreach($data as $key => $value){
            if($value['lm_top']){
                $date = strtotime('now') + $value['lm_top'] - $min;
                $value['pub_date'] = date('Y-m-d H:i:s',$date);
            }
            $result += $db->save($value);
        }
        flushMemcache();
        $this->ajaxReturn($result);
    }
    public function setEssence(){
        parent::getList();
        $db = M($this->_db_table,null,$this->_db_name);
        $data = [
            'article_id' => $this->article_id,
            'is_good'     => $this->type == 1 ? 1 :0
        ];

        $result = $db->save($data);
        if($result){
            flushMemcache();
            echo 0;
        }else{
            echo -1;
        }
    }
    public function delete(){
        $db = M($this->_db_table,null,$this->_db_name);
        $article_id = $_POST['article_id'];
        $result = $db->delete($article_id);
        flushMemcache();
        $this->ajaxReturn($result);
    }

    public function banner(){
        $table_title = [
            'banner_forum_id'=> '栏目分类',
            'banner_title'   => '标题',
            'banner_pic_url' => '图片',
            'banner_web_url' => '跳转网页地址',
            'insert_dt'      => '添加时间',
            'banner_order_id'=> '排序',
            'action'         => '操作提示'
        ];
        $this->assign("table_title",$table_title);//列表页文章标题
        //获取分类栏目
        $forumList = $this->_getForumList();
        $forumList[] = ['forum_id_lm'=>'7','forum_name_lm'=>'精选栏目'];
        $this->assign('forum_list',$forumList);//分页栏目
        //获取文章来源
        // $this->assign('author_list',$this->_getAuthorList());//文章来源
        $this->display();
    }

    public function getInfoBannerList(){
        parent::getList();

        $db = M('t_banner_picture_cfg',null,$this->_db_name);
        $sql = "SELECT
        banner_id,
        CASE banner_forum_id
        WHEN 1 THEN '日常调理'
        WHEN 2 THEN '保健饮食'
        WHEN 3 THEN '育儿频道'
        WHEN 4 THEN '减肥健身'
        WHEN 5 THEN '心理健康'
        WHEN 6 THEN '学术会议'
        WHEN 7 THEN '精选栏目'
        END AS 'banner_forum_id',
        banner_title,
        banner_pic_url,
        banner_web_url,
        banner_order_id,
        DATE_FORMAT(insert_dt,'%Y-%m-%d %H:%i:%s') AS 'insert_dt'
        FROM t_banner_picture_cfg WHERE is_delete=0  ";

        if($this->forum_id){
            $sql .= " and banner_forum_id=".$this->forum_id;
        }
        $sql .= " ORDER BY banner_order_id DESC,insert_dt DESC;";
        $data = $db->query($sql);
        foreach($data as $key=>$val){
            $val['banner_order_id'] = "<input rel='{$val['banner_id']}'onkeyup=\"this.value=this.value.replace(/\D/g,'')\"  onafterpaste=\"this.value=this.value.replace(/\D/g,'')\"  size=\"3\" maxlength='3'  type='text' value='{$val['banner_order_id']}' name='banner_order_id' />";
            $val["action"] = '
	 			<a href="bannerEdit/?banner_id='.$val["banner_id"].'" class="easyui-linkbutton" iconCls="icon-edit" plain="true">编辑</a>
				<a href="javascript:void(0);" onclick="del('.$data[$key]["banner_id"].');" class="easyui-linkbutton" iconCls="icon-cancel" plain="true">删除</a>';
            $val['banner_pic_url'] = "<img src='{$val['banner_pic_url']}' width='50px' onclick='view_pic(\"{$val['banner_pic_url']}\");' />";
            $data[$key] = $val;
        }

        $table_data["total"] = 1;
        $table_data["rows"] = $data;
        $this->ajaxReturn($table_data);
    }

    public function del(){
        $table = M("t_banner_picture_cfg",null,$this->_db_name);
        $id = $_POST["id"];
        $data = array(
            "banner_id" 	=> $id,
            'is_delete'     => 1
        );
        $result = $table->save($data);
        flushMemcache();
        $this->ajaxReturn($result);
    }
    public function bannerEdit(){
        $db = M("t_banner_picture_cfg",null,$this->_db_name);
        if(IS_POST){
            $data = $_POST;
            if($data['banner_id']){
                $result = $db->save($data);
            }else{
                $data['is_delete'] = 0;
                $data['insert_dt'] = date('Y-m-d H:i:s');
                $result = $db->add($data);
            }
            $this->log_trace(print_r($data,true));
            flushMemcache();
            $this->ajaxReturn($result);
        }
        parent::getList();
        $forumList = $this->_getForumList();
        $forumList[] = ['forum_id_lm'=>'7','forum_name_lm'=>'精选栏目'];
        $this->assign('forum_list',$forumList);//分页栏目
        $detail = $this->banner_id ? $db->where("banner_id = {$this->banner_id}")->find() : array();
        $title = $this->banner_id ? "编辑" : "添加";
        $this->assign('detail',$detail);
        $this->assign('title',$title);
        //图片上传
        $hfs = new HfsModel(HfsModel::OP_TYPE_INFORMATION_IMG);
        $hfs->addParm("file_name", getMillisecond().".jpg");
        $upload_addr = $hfs->getJson();
        $down_addr = $hfs->getDownUrl();
        $upload_addr = __URL__."/uploadPic?json=".$upload_addr;
        $this->assign("upload_addr",$upload_addr);
        $this->assign("down_addr",$down_addr);
        $this->display();
    }
    public function bannerSaveOrder(){
        $db = M("t_banner_picture_cfg",null,$this->_db_name);
        $data = $_POST['data'];
        $result = 1;
        foreach($data as $key => $value){
            $result += $db->save($value);
        }
        flushMemcache();
        $this->ajaxReturn($result);
    }
}