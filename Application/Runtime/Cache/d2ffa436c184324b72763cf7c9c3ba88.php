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
    <script type="text/javascript" src="__PUBLIC__/js/easyui_common.js"></script>
      <div id="tb" style="padding:5px;height:auto;">
		<div style="margin: 10px 0 10px 10px" >
		<!-- form表单 指定id和name之后会自动带到服务器 -->
		<form name="searchform" method="get" action="" id ="searchform" style="height:20px;">  
		<div style="display: none" >   
	    	<input class="easyui-textbox" id="file_title" name="file_title" data-options="prompt:'按标题查找'" style="width:140px;height:32px">
	    		    	推荐起始时间查找：
	    		<input class="easyui-datetimebox" id="start_time" name="start_time"   data-options="prompt:'推荐起始时间从',formatter:DateTimeformat,parser:myparser" style="width:160px;height:32px"></input>
	    		<input class="easyui-datetimebox" id="end_time" name="end_time"  data-options="prompt:'推荐起始时间到',formatter:DateTimeformat,parser:myparser" style="width:160px;height:32px"></input>
	    		
	        <a href="javascript:void(0);" id="submit_search" class="easyui-linkbutton" iconCls="icon-search"  style="width:60px;height:32px">查询</a>
	      </div> 
	        <a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-add"  style="height:32px;" onclick="javascript:window.location.href='__URL__/add'">上传新文件</a>
	         
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

<!-- 
		//设置datetime控件时间
		var curr_time = new Date();
		var time = DateTimeformat(curr_time);
		$("#start_time").datetimebox('setValue', time);
		var timestamp =Date.parse(curr_time);
		timestamp += 30*24*3600*1000;  
		var new_time = new Date(timestamp); 
		var new_time = DateTimeformat(new_time);
  		$("#end_time").datetimebox("setValue",new_time);
  		$("#submit_search").click();
 -->
 <script>
 function play(addr){
	var res_addr = '__URL__/play/'+addr;	
　	window.open (res_addr, "newwindow", "height=100, width=400, top=100, left=300, toolbar=no, menubar=no, scrollbars=no, resizable=no,location=no, status=no");
 }
 </script>
    </body>
</html>