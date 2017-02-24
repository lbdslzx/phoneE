<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>贵健康管理系统</title>
	<link rel="stylesheet" type="text/css" href="__PUBLIC__/easyui/themes/default/easyui.css">
	<link rel="stylesheet" type="text/css" href="__PUBLIC__/easyui/themes/icon.css">
	<script type="text/javascript" src="__PUBLIC__/easyui/locale/easyui-lang-zh_CN.js"></script>
	<script type="text/javascript" src="__PUBLIC__/easyui/jquery.min.js"></script>
	<script type="text/javascript" src="__PUBLIC__/easyui/jquery.easyui.min.js"></script>
	<script type="text/javascript" src="__PUBLIC__/js/easyui_common.js"></script>
	
	<script type="text/javascript">
		function clickTab(){
		    $('#tt').tabs({
		        border:false,
		        onSelect:function(title){
		       		alert(title+' is selected');
		        }
		        });
		}
	    function addTab(title,href){
			var height = document.documentElement.clientHeight;
			height = height - 100;
	    	var is_exist = $('#tt').tabs("exists",title);  // get selected panel
	    	// add a new tab panel
	    	if(is_exist){
	    		$('#tt').tabs('select',title);
	    		var currTab =  $('#tt').tabs('getSelected'); //获得当前tab
	    	    var url = $(currTab.panel('options').content).attr('src');
	    	    $('#tt').tabs('update', {
	    	      tab : currTab,
	    	      options : {
	    	       content : '<iframe id="main_frame" src="'+href+'" frameborder="0" style="width:100%;height:'+height+'px"></iframe>'
	    	      }
	    	     });
	    	}else{
			    $('#tt').tabs('add',{
				    title:title,
				    content:'<iframe id="main_frame" src="'+href+'" frameborder="0" style="width:100%;height:'+height+'px"></iframe>',
//					href:href,
				    closable:true,
				    border:false,
				    tools:[{
					    iconCls:'icon-reload',
					    handler:function(){
					    	// call 'refresh' method for tab panel to update its content
					    	var tab = $('#tt').tabs('getSelected');  // get selected panel
					    	var href = $(tab.panel('options').content).attr('src');
					    	$('#tt').tabs('update', {
					    		tab: tab,
					    		options: {
					    			content:'<iframe id="main_frame" src="'+href+'" sctroll="auto" frameborder="0" style="width:100%;height:'+height+'px"></iframe>',
					    		}
					    	});
				    	}
				    }]
			    });
		    }
	    	return false;
	    }
	
	    function closeOthers(){
	    	var tab = $('#tt').tabs('getSelected');  // get selected panel
	    	var title = tab.panel('options').title
	    	closeAll(title);
	    }
	    function closeAll(title) {
	        $(".tabs li").each(function(index, obj) {
	              //获取所有可关闭的选项卡
	            var tab = $(".tabs-closable", this).text();
	            if(tab != title){
	            	$(".easyui-tabs").tabs('close', tab);
	            }
	        });
	        $("#close").remove();//同时把此按钮关闭
	        var tab = $('#tt').tabs('getSelected');  // get selected panel
	        var index = $('#tt').tabs('getTabIndex',tab);
	        if(index < 0){
	        	addTab("欢迎","__URL__/showTpl/main");	
	        }
	      }
	
	
	    function changepwd(){
	    	addTab("修改密码","__URL__/showTpl/password_edit");
	    }
	    //初始化操作
	    window.onload= function(){
	    	addTab("欢迎","__URL__/showTpl/main");
	    	$(function(){
				$(".tabs-header").bind('contextmenu',function(e){
					e.preventDefault();
					$('#mm').menu('show', {
						left: e.pageX,
						top: e.pageY
					});
				});
			});
	    }
	</script>
</head>
<body class="easyui-layout">
	
	<div data-options="region:'west',split:true,border:false,title:'菜单',href:'__URL__/createMenu'" style="width:180px; height: 80%;overflow:hidden;">
	<!-- 
	<iframe src="__URL__/createMenu" noResize scrolling="no" border="0" frameSpacing="0" frameborder="0" marginwidth="10" marginheight="0" width="100%" height="95%"></iframe>	
	-->
	</div>
	<!-- 主界面 -->
	<div data-options="region:'center',title:'当前用户:<?php echo ($user_name); ?> <div style=\'float:right;margin-right:60px\'>    <a id=\'changepwd\' onclick=\'changepwd();\' href=\'#\' >修改密码 </a> &nbsp;&nbsp;&nbsp; <a style=\'margin-right:0\' href=__APP__/Login/logOut>退出登录 </a> </div>'">
	
	    <div id="tt" class="easyui-tabs" style="width:100%;"  border="false">
		 </div>
	</div>
	
	<div data-options="region:'south',border:true" style="height:28px" >
		<iframe src="__URL__/showTpl/footer" frameborder="0" width="100%" height="100%"></iframe>
	</div>
	
	<div id="mm" class="easyui-menu" style="width:120px;">
		<div data-options="iconCls:'icon-cancel'" onclick="javascript:closeOthers();">关闭其他</div>
		<div data-options="iconCls:'icon-cancel'" onclick="javascript:closeAll('_');">关闭全部</div>
	</div>

</body>
</html>