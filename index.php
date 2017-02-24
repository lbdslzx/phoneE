<?php
/*
 * 
 * 全局入口文件
 */
require_once 'global.php';

define('APP_DEBUG',True);
//定义访问模块名称
define('BIND_MODULE','Admin');
// 定义配置文件位置
define('CONF_PATH',APP_ROOT.'Conf/');

$base = pathinfo(dirname(__FILE__));
define('APP_NAME','./'.$base['basename']);
// 定义应用目录
define('APP_PATH','./Application/');

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

