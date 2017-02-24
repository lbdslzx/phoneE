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
		<div style="margin: 10px 0 10px 10px" >
		<!-- form表单 指定id和name之后会自动带到服务器 -->
		<form name="searchform" method="get" action="" id ="searchform"> 
		<select class="easyui-combobox" name="server_ip" >
	    			<option value="">全部</option>
					<?php if(is_array($server)): $i = 0; $__LIST__ = $server;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value='<?php echo ($key); ?>'><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
	    			</select>  
		报告时间从<input class="easyui-datebox" id="begin_dt"  name="begin_dt"  data-options="required:true,editable:false" ></input>
		
		到<input class="easyui-datebox" id="end_dt"  name="end_dt"  data-options="required:true,editable:false" ></input> 
	    	<input class="easyui-textbox"  name="user_id" data-options="prompt:'请输入用户ID'" style="width:170px;height:32px;margin-left: 300px;">
    		<input class="easyui-textbox"  name="app_version_code" data-options="prompt:'客户端版本'" style="width:170px;height:32px;margin-left: 300px;">
    		
    		
	        <a href="javascript:void(0);" id="submit_search" class="easyui-linkbutton" iconCls="icon-search"  style="width:60px;height:32px;margin-left: 10px;">查询</a>
	        
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

<script>
function view(id){
	window.parent.addTab(id+"详细","__URL__/view?id="+id);
}
$(function(){
	//设置datetime控件时间
	var curr_time = new Date();
//	var time = DateTimeformat(curr_time);
	var time = new Date().Format("yyyy-MM-dd");
	$("#end_dt").datebox("setValue",time);
	var timestamp =Date.parse(curr_time);
	timestamp -= 15*24*3600*1000;  
	var new_time = new Date(timestamp); 
	var new_time = DateTimeformat(new_time);
	$("#begin_dt").datebox('setValue',new_time );
//	$("#submit_search").click();
 });
</script>
    </body>
</html>