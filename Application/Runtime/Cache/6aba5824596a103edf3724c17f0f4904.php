<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>用户反馈</title>
    <link rel="stylesheet" type="text/css" href="__PUBLIC__/easyui/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="__PUBLIC__/easyui/themes/icon.css">
<script type="text/javascript" src="__PUBLIC__/easyui/jquery.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/easyui/locale/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/easyui_common.js"></script>

</head>
<body>
<div id="tb" style="padding:5px;height:auto;">
    <div style="margin: 10px 0 10px 10px" >
        <!-- form表单 指定id和name之后会自动带到服务器 -->
        <form name="searchform" method="get" action="" id ="searchform" style="height:20px;margin-bottom: 20px">
            <label for="phone_number">联系电话：</label>
            <input class="easyui-textbox" id="phone_number" name="phone_number" data-options="prompt:'按联系电话查询'" style="width:140px;height:32px"/>
            <label for="name">用户姓名：</label>
            <input class="easyui-textbox" id="name" name="name" data-options="prompt:'按用户姓名查询'" style="width:140px;height:32px"/>
            <a href="javascript:void(0);" id="submit_search" class="easyui-linkbutton" iconCls="icon-search"  style="width:60px;height:32px">查询</a>
        </form>
    </div>
</div>

<table id="dg"  style="width:100%; min-height: 500px"  title="查询结果"
       data-options="rownumbers:false,
			singleSelect:true,
			pagination:true,
			ctrlSelect:true,
			fitColumns:true,
			loadMsg:'正在加载,请等待....',
			url:'__URL__/getList',
			method:'post',
			toolbar:'#tb',
			">
    <thead>
    <tr>
        <?php if(is_array($table_title)): $i = 0; $__LIST__ = $table_title;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><th data-options="field:'<?php echo ($key); ?>',align:'center',width:'10px'"><?php echo ($vo); ?></th><?php endforeach; endif; else: echo "" ;endif; ?>
    </tr>
    </thead>
</table>

<script type="text/javascript">
    function openW(fb_id){
        $.ajax({
            type: "POST",
            url:'__URL__/getFbInfo',
            data:{'fb_id':fb_id},
            dataType: "json",
            success: function(data){
                $('#fb_id').val(data.fb_id);
                $('#user_id').val(data.user_id);
                $('#fb_content').text(data.content);
                $('#w').window('open');
            }
        });
    }

    var btnLock = false;
    function semdReply(){
        var self = $('#sendBtn');
        if(btnLock){
            return;
        }

        var reply_content = $('#reply_content').val().trim();
        var fb_id = $('#fb_id').val();
        var user_id = $('#user_id').val();
        if(reply_content.length == 0){
            $.messager.alert('提示','回复内容不能为空!');
            return false;
        }

        if(reply_content.length > 200){
            $.messager.alert('提示','回复内容不能超过200字!');
            return false;
        }

        self.text('发送中');
        self.css('line-height','23px');
        $('#reply_content').attr('readonly',true);
        btnLock = true;//加按钮锁，防止重复点击

        setTimeout(function(){
            $.ajax({
                type: "POST",
                url:'__URL__/semdReply',
                data:{'content':reply_content,'fb_id':fb_id,'user_id':user_id},
                dataType: "json",
                success: function(data){
                    if(data){
                        closeW();
                        $('#dg').datagrid('reload');
                    }
                },
                error:function(){
                    self.val('发送');
                    btnLock = false;
                }
            });
        },1000);
    }

    function closeW(){
        $('#sendBtn').text('发送');
        btnLock = false;
        $('#reply_content').attr('readonly',false);
        $('#reply_content').val('');
        $('#w').window('close');
    }
</script>

<div id="w" class="easyui-window" title="选择在线医生" data-options="modal:true,closed:true,iconCls:'icon-save',buttons:'#bb22'" style="width:600px;height:440px;padding:10px;">
    <div>
        <span>用户反馈内容：</span>
        <input type="hidden" id="fb_id">
        <input type="hidden" id="user_id">
        <p>
            <textarea name="fb_content" id="fb_content" style="width: 100%;height: 120px;resize: none" readonly></textarea>
        </p>
        <span>输入回复内容：</span>
        <p>
            <textarea name="fb_content" id="reply_content" style="width: 100%;height: 120px;resize: none"></textarea>
        </p>
        <p style="float: right">
            <a href="javascript:void(0);" class="easyui-linkbutton"  onclick="closeW()" style="width:60px;height:25px ;">取消</a>
            <a href="javascript:void(0);" class="easyui-linkbutton" id="sendBtn"  style="width:60px;height:25px;" onclick="semdReply()">发送</a>
        </p>
    </div>
</div>

</body>
</html>