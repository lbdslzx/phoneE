<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
   <title>底部</title>
   <link href="__PUBLIC__/css/console.css" rel="stylesheet" type="text/css" />
	<script language="JavaScript">
	<!--
	var theDay = new Date();
	function show(){
	 var divClock=document.getElementById("divLiveClock");
	 if (!divClock)
	 return;
	 var Digital=new Date();
	 var seconds=Math.abs(Math.floor((theDay.getTime() - Digital.getTime())/1000));
	 var minutes=Math.floor(seconds/60);
	 var hours=Math.floor(minutes/60);
	 var Dseconds = seconds % 60;
	 var Dminutes = minutes % 60;
	 var Dhours = hours % 60;

	//更改字体大小
	myclock="[您已登入系统：&nbsp;"+Dhours+"&nbsp;小时&nbsp;"+Dminutes+"&nbsp;分&nbsp;"+Dseconds+"&nbsp;秒]";
        divClock.innerHTML=myclock;
	setTimeout("show()",1000);
	}
	//-->
	</script>
</head>

<body onload="show();">
   <div class="right_footer">
		<span id="divLiveClock"></span>  
		<span class="liveright">Copyright 2013 Longmaster All Rights Reserved&nbsp;</span>
	</div>
</body>
</html>