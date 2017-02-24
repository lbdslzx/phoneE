<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>首页Banner管理（顶部）</title>
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
            <a href="__URL__/topEdit" class="easyui-linkbutton" iconCls="icon-add"  style="height:32px;float: right;margin-right: 60px">添加</a>
            <a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-save" onclick="top_save_order()"  style="height:32px;float: right;margin-right: 60px">保存</a>
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
			url:'__URL__/getTopList',
			method:'post',
			toolbar:'#tb',
			">
    <thead>
    <tr>
        <?php if(is_array($table_title)): $i = 0; $__LIST__ = $table_title;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><th data-options="field:'<?php echo ($key); ?>',align:'center',width:'10px'"><?php echo ($vo); ?></th><?php endforeach; endif; else: echo "" ;endif; ?>
    </tr>
    </thead>
</table>
</body>
<script type="text/javascript">
    function view_pic(url){
        var data = "<img src='"+url+"' id='pic' height='300px' />";
        $.messager.alert('查看图片',"",'info');
        $(".messager-window").css("width","");
        $(".messager-body").css("width","");
        $(".window-header").css("width","");
        $(".messager-window").css("left","100px");
        $(".messager-window").css("top","40px");
        $(".window-shadow").css("display","none");
        $(".messager-body").html(data);
    }
    function top_del(id){
        $.messager.confirm('提示', '确定要删除吗?', function(r){
            if(r){
                $.ajax( {
                    url: '__URL__/topDel',
                    type: 'post',
                    data: {'id':id},
                    cache:false,
                    dataType:'json',
                    success: function (data){
                        if(data >0 ){ //影响行大于0则成功
                            m_info("删除成功");
                            $('#dg').datagrid({ queryParams: form2Json("searchform") });
                        }else{
                            m_erro("删除失败");
                        }
                    },
                    error: function (data, status, e){
                        m_erro(e);
                    }
                });
            }
        });
    }
    function top_save_order(){
        var data = new Array();
        $("table").find('input').each(function(){
            if($(this).attr('rel') != undefined) {
                var d = {'id': $(this).attr('rel'), 'order_id': $(this).val()};
                data.push(d);
            }
        });
        console.info(data);
        $.ajax( {
            url: '__URL__/topSaveOrder',
            type: 'post',
            data: {'data':data},
            cache:false,
            dataType:'json',
            success: function (data){
                if(data >0 ){ //影响行大于0则成功
                    m_info("更新成功");
                    $('#dg').datagrid({ queryParams: form2Json("searchform") });
                }else{
                    m_erro("更新失败");
                }
            },
            error: function (data, status, e){
                m_erro(e);
            }
        });
    }
</script>
</html>