<?php 

function flushMemcache(){
	$addr = C("mem_flush_addr");
	$result = file_get_contents($addr);
}

/**
 * 返回中文是否删除，0未删除，1 已删除
 * @param unknown $flag
 */
function chn_is_del($flag){
	return $flag ==1?"已删除":"正常";
}
function chn_is($flag){
	return $flag ==1?"是":"否";
}
function chn_gender($flag){
	return $flag ==0?"男":"女";
}
function chn_phone_os($flag){
	return $flag ==0?"IOS":"Android";
}

/**
 * 转换为中文显示
 * @param array $arr 自定义数组
 * @param int $flag
 * @return unknown
 */
function chn_flag($arr,$flag){
	return array_key_exists($flag, $arr)?$arr[$flag]:$flag;
}
/**
 * 设备ID  在新版本中他们用做device_type
 * @param unknown $flag
 * @param bool $is_cfg 如果为true，则返回查询数组
 * @return Ambigous <unknown, string>
 */
function chn_device_id($flag,$is_cfg = FALSE){
	$device_arr = array(
		"0"	=> "手动输入",
		"1"	=> "血压仪",
		"2"	=> "智能秤",
		"3"	=> "血糖仪",
		"4"	=> "健康手环",
		"5"	=> "康康血压仪",
		"6"	=> "PICOO智能秤 ",
		"7"	=> "GSM血糖仪(爱奥乐)",
		"8" => "GSM血压仪",
		"100"=>	"苹果内置计步器"
	);
	if ($is_cfg){
		return $device_arr;
	}
	return array_key_exists($flag, $device_arr)?$device_arr[$flag]:$flag;
}
function formateURIAddr($addr){
	$end = substr($addr, -1);
	if($end != "/"){
		$addr .= "/";
	}
	return $addr;
}
/**
 * 获取精确到微妙的时间
 * @return number
 */
function getMillisecond() {
	list($s1, $s2) = explode(' ', microtime());
	return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
}

/**
 * 时间段 1-上午；2-下午；3-夜间；4-全天
 * @return string
 */
function chn_time_range($param){
	switch ($param){
		case 1:
			$str = '上午';
			break;
		case 2:
			$str = '下午';
			break;
		case 3:
			$str = '夜间';
			break;
		case 4:
			$str = '全天';
			break;
		default:
			$str = '';
			break;
	}
	return $str;
}

/**
 *
 * 就诊卡类型 1-医院就诊卡，2-居民健康卡，3-暂无就诊卡
 * @return string
 */
function chn_widget_id($param){
	switch ($param){
		case 1:
			$str = '医院就诊卡';
			break;
		case 2:
			$str = '居民健康卡';
			break;
		default:
			$str = '暂无就诊卡';
			break;
	}
	return $str;
}