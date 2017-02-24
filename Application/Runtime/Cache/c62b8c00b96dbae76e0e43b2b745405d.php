<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>消息推送</title>
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
            <label for="lable_name">消息类型：</label>
            <select class="easyui-combobox" id ="lable_name" name="lable_name" style="width:140px;height:32px;">
                <option value="全部">全部</option>
                <?php if(is_array($lable)): foreach($lable as $key=>$vo): ?><option value="<?php echo ($vo["lable_name"]); ?>"><?php echo ($vo["lable_name"]); ?></option><?php endforeach; endif; ?>
            </select>
            <label for="push_state">推送状态：</label>
            <select class="easyui-combobox" id ="push_state" name="push_state" style="width:140px;height:32px;">
                <option value="0">不限</option>
                <option value="2">推送中</option>
                <option value="3">已推送</option>
                <option value="1">未推送</option>
            </select>
            <label for="message_title">关键字：</label>
            <input class="easyui-textbox" id="message_title" name="message_title" data-options="prompt:'按消息标题查询'" style="width:140px;height:32px"/>

            <a href="javascript:void(0);" id="submit_search" class="easyui-linkbutton" iconCls="icon-search"  style="width:60px;height:32px">查询</a>
            <a href="__URL__/edit" class="easyui-linkbutton" iconCls="icon-add"  style="height:32px;float: right;margin-right: 60px">添加</a>
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
    function goSend(tempId,type){
        $.messager.confirm('提示', '确定要推送吗?', function(r){
            if(r){
                $.ajax( {
                    url: '__URL__/messagePush',
                    type: 'post',
                    data: {'id':tempId,'type':type},
                    cache:false,
                    dataType:'json',
                    success: function (data){
                        if(data >0 ){ //影响行大于0则成功
                            m_info("推送成功");
                            $('#dg').datagrid({ queryParams: form2Json("searchform") });
                        }else{
                            m_erro("推送失败");
                        }
                    },
                    error: function (data, status, e){
                        m_erro("推送失败");
                        $('#dg').datagrid({ queryParams: form2Json("searchform") });
                        //m_erro(e);
                    }
                });
            }
        });
    }
</script>
</body>
</html>