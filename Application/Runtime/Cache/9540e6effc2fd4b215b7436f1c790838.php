<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>首页Banner管理（中间）</title>
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
            <label style="color: red">注：每项均可执行开启和关闭操作</label>
            <a href="__URL__/centerEdit" class="easyui-linkbutton" iconCls="icon-add"  style="height:32px;float: right;margin-right: 60px">添加</a>
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
			url:'__URL__/getCenterList',
			method:'post',
			toolbar:'#tb',

			">
    <thead>
    <tr>
        <?php if(is_array($table_title)): $i = 0; $__LIST__ = $table_title;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key!="doc_id"){ ?>
                <th data-options="field:'<?php echo ($key); ?>',align:'center',width:'10px',nowrap:false"><?php echo ($vo); ?></th>
            <?php }else{ ?>
                <th data-options="field:'<?php echo ($key); ?>',align:'center',width:'',nowrap:false"><?php echo ($vo); ?></th>
            <?php } endforeach; endif; else: echo "" ;endif; ?>
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
    function center_change_state(id,state){
        $.messager.confirm('提示', '之前启用的模块会自动关闭，确定启用该模块吗?', function(r){
            if(r){
                $.ajax( {
                    url: '__URL__/centerChangState',
                    type: 'post',
                    data: {'module_id':id,'cfg_state':state},
                    cache:false,
                    dataType:'json',
                    success: function (data){
                        if(data >0 ){ //影响行大于0则成功
                            m_info("启用成功");
                            location.reload();
                        }else{
                            m_erro("启用失败");
                        }
                    },
                    error: function (data, status, e){
                        m_erro(e);
                    }
                });
            }
        });
    }


    function altSort(id,pid){
        //alert(id+"---"+pid);
        var v=$("#"+id).val();
        var datas={"order_id":v,"module_id":pid};
        $.ajax({
            type: "POST",
            url: "__URL__/upSort/"+Math.random(),
            data: datas,
            success: function(msg){
                if(msg=="y"){
                    //$.messager.alert("提示","操作成功");
                    $('#dg').datagrid('reload');
                }else{
                    //$.messager.alert("提示","操作失败");
                }
            }
        });


    }

    //删除首页banner
    function delBanner(id){

        $.messager.confirm('确认','您确认想要删除记录吗？',function(r){
            if (r){

                var datas={"module_id":id};
                $.ajax({
                    type: "POST",
                    url: "__URL__/delBanner/"+Math.random(),
                    data: datas,
                    success: function(msg){
                        if(msg=="y"){
                            //$.messager.alert("提示","操作成功");
                            $('#dg').datagrid('reload');
                        }else{
                            $.messager.alert("提示","操作失败");
                        }
                    }
                });


            }
        });
    }

</script>
</html>