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
    <body >
	<div class="easyui-panel" title="<?php echo ($title); ?>" style="width:100%;">
		<div style="padding:10px 60px 20px 60px">
	    <form id="ff" method="post" action="__URL__/edit">
	    	<table cellpadding="5">
	    			<tr>
	    			<td>索引字母：</td>
	    			<td>
	    			<input class="easyui-textbox" type="text" name="index_letter" data-options="required:true"></input>
	    			</td>
	    		</tr>
	    		<tr>
	    			<td>所属分类:</td>
	    		<td>
					<select class="easyui-combobox" name="father_id">
							<option value="0">一级分类</option>
		    				<?php if(is_array($f_cat)): $i = 0; $__LIST__ = $f_cat;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["app_disease_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
		    			</select>
		    			</td></tr>
	    		<!-- 根据实际情况添加其他列 -->
	    		<tr>
	    			<td>39网疾病名称 ：</td>
	    			<td><input class="easyui-textbox" type="text" name="net_disease_name"  data-options="required:true"></input></td>
	    		</tr>
	    		<tr>
	    			<td>39健康管家疾病名称 ：</td>
	    			<td><input class="easyui-textbox" type="text" name="app_disease_name"  data-options="required:true" ></input></td>
	    		</tr>
	    		</table>
	    		<div style="display: none">
			    <input class="easyui-textbox" name="id" value=""></input>
			    </div>
	    	</form>
	    		
	     <div style="padding:5px">
	     	<a href="javascript:void(0);" class="easyui-linkbutton"  onclick="submitForm()" data-options="iconCls:'icon-add'" style="width:100px;height:32px">保存</a>
	      <a href="#" class="easyui-linkbutton" iconCls="icon-undo" style="width:100px;height:32px" onclick="javascript:window.location.href='__URL__/index'">返回</a>
	    </div>
	    </div>
	</div>
	<script>
		function submitForm(){
			$('#ff').form('submit',{
				onSubmit:function(){
					var json = form2Json("ff");
					var form_valid = $(this).form('enableValidation').form('validate');
					if(!form_valid ){
						return false; 
					}
					json = JSON.stringify(json);
					 $.post("__URL__/edit/"+Math.random(), "json=" + json, function(data) {
			             if (data >0) {
			            	 $.messager.alert("提示","操作成功");
			             }else{
			            	 $.messager.alert("提示","操作失败");
			             }
			         });
					 return false;
				}
			});
		}
		$(function(){
			$('#ff').form('load', <?php echo ($data); ?>);			
		});

	</script>
	
    </body>
</html>