<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Insert title here</title>
        <link rel="stylesheet" type="text/css" href="__PUBLIC__/easyui/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="__PUBLIC__/easyui/themes/icon.css">
<script type="text/javascript" src="__PUBLIC__/easyui/jquery.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/easyui/locale/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/easyui_common.js"></script>

    </head>
    <body>
    
      <div id="tb" style="padding:5px;height:auto;">
		<div style="margin: 20px 0 20px" >
		<!-- form表单 指定id和name之后会自动带到服务器 -->
		<form name="searchform" method="post" action="" id ="searchform">    
	    	<input class="easyui-textbox" id="admin_name" name="admin_name" data-options="prompt:'请输入管理员名'" style="width:140px;height:32px">
	        <a href="#" id="submit_search" class="easyui-linkbutton" iconCls="icon-search">查询</a>
 		</form>
		</div>
	</div>
	<!-- 展示数据部分 -->
        <table id="dg"  style="width:100%; "  title="查询结果" 
			data-options="rownumbers:true,
			singleSelect:true,
			pagination:true,
			ctrlSelect:true,
			fitColumns:true,
			loadMsg:'正在加载,请等待....',
			url:'__URL__/getAdminList',
			method:'get',
			toolbar:'#tb',
			">
		<thead>
			<tr>
				<?php if(is_array($table_title)): $i = 0; $__LIST__ = $table_title;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><th data-options="field:'<?php echo ($key); ?>',align:'center',width:'10px'"><?php echo ($vo); ?></th><?php endforeach; endif; else: echo "" ;endif; ?>
			</tr>
		</thead>
	</table>


    </body>
</html>