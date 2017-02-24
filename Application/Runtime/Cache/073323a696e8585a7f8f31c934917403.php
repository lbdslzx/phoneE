<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>贵健康管理系统</title>
<link rel="stylesheet" type="text/css" href="__PUBLIC__/css/common_style.css" />
<script type="text/javascript" src="__PUBLIC__/js/jquery-1.10.2.min.js"></script>
<script>
    $(document).ready(function(){
        
        $(".hint_whole").hide();
        //验证码切换
        $("#change_code").click(function(){
            mytime = new Date();
            $("#v_code").attr("src","__URL__/verify/"+mytime.getTime()); 
        });
        $("#f_user").focus();

    })
    
    //用户输入信息验证
    function check_info(){
        if(!$.trim($("#f_user").val())){
            $(".hint_whole").show();
            $(".hint_content").html("请输入用户名");
            return false;
        }else{
            if($("#f_user").val().length > 10){
                $(".hint_whole").show();
                $(".hint_content").html("用户名输入框，最大长度10个字符");
                return false;
            }
        }    
        if(!$.trim($("#f_pwd").val())){
            $(".hint_whole").show();
            $(".hint_content").html("请输入密码");
            return false;
        }else{
            if($("#f_pwd").val().length > 20){
                $(".hint_whole").show();
                $(".hint_content").html("密码输入框，最大长度20个字符");
                return false;
            }
        }
        if(!$.trim($("#f_code").val())){
            $(".hint_whole").show();
            $(".hint_content").html("请输入验证码");
            return false;
        } 
    }
    
</script>
</head>

<body>
<div class="common_container">
        <div class="entry_left">
            <div class="contact_box">            	
                <p class="contacts_phone">联系电话：0851 - 3842119（8815）</p>
            </div>
            <div class="contact_box">            	
                <p class="contacts_QQ">在线客服：</p>
                <a href="http://wpa.qq.com/msgrd?v=3&uin=1244543003&site=qq&menu=ye" target="_blank" alt="点击这里给我发消息" title="点击这里给我发消息"></a>
            </div>
        </div>
        
        <form id="login_form" action="__URL__/login" method="post" onsubmit="return check_info();">
            <div class="entry_right">
                
                <div class="entry_details">
                    <div class="hint_whole">
                        <div class="hint_left"></div>
                        <div class="hint_content"><?php echo ($msg); ?></div>
                        <div class="hint_right"></div>
                    </div>
                </div>
                
                <div class="entry_details">
                    <p>用户名：</p>
                    <div class="circle_left"></div>
                    <div class="circle_content"><input id="f_user" name="f_user" type="text" /></div>
                    <div class="circle_right"></div>
                </div>
                <div class="entry_details">
                    <p>密  码：</p>
                    <div class="circle_left"></div>
                    <div class="circle_content"><input id="f_pwd" name="f_pwd" type="password" /></div>
                    <div class="circle_right"></div>
                </div>
                <div class="entry_details">
                    <p>验证码：</p>
                    <div class="circle_left"></div>
                    <div class="circle_proving"><input id="f_code" name="f_code" type="text" /></div>
                    <div class="circle_right"></div>
                    <span><img id="v_code" src='__URL__/verify'  alt="验证码"/></span>
                    <a id="change_code" href="#" title="刷新验证码">看不清，换一张</a>
                </div>
                <div class="entry_button"><input type="submit" value=""/></div>
            </div>
        </form>
        <!--aluabr底部-->
<div class="footer">
    <p>朗玛公司 版权所有</p>
    <p>Copyright © 2013 Longmaster Inc. All Rights Reserved.</p>
    <a href="#">网站备案/许可证号：黔B2-20090050-4 </a>
</div>
</div>
</body>
</html>