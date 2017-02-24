var url_path = window.location.href;
var end = url_path.indexOf("index.php");
var url = url_path.substr(0,end+9); //获取当前url
var action = url_path.substr(end);
//var action = url+"/"+action.substr(10,action.indexOf("/")-4);
var action_split = action.split("/");
action = action_split[1];
//将表单数据转为json
    function form2Json(id) {
        var arr = $("#" + id).serializeArray();
        var jsonStr = "";

        jsonStr += '{';
        for (var i = 0; i < arr.length; i++) {
            jsonStr += '"' + arr[i].name + '":"' + arr[i].value + '",';
        }
        jsonStr = jsonStr.substring(0, (jsonStr.length - 1));
        jsonStr += '}';

        var json = JSON.parse(jsonStr);
        return json;
    }
    //格式化时间
	function DateTimeformat(date){

		var y = date.getFullYear();
		var m = date.getMonth()+1;
		var d = date.getDate();
		var h = date.getHours();
		var i = date.getMinutes();
		var s = date.getSeconds();
		var str = y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d)+' '+(h<10?('0'+h):h)+':'+(i<10?('0'+i):i)+':'+(s<10?('0'+s):s);
		return str;
	}
	 function myparser(str){
		 if (!str) return new Date();
		 var strArray=str.split(" ");
		 var strDate=strArray[0].split("-");
		 var strTime=strArray[1].split(":");
		 var dateTime=new Date(strDate[0],(strDate[1]-parseInt(1)),strDate[2],strTime[0],strTime[1],strTime[2]); 
		 return dateTime;
	}
	// 对Date的扩展，将 Date 转化为指定格式的String
	// 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符， 
	// 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字) 
	// 例子： 
	// (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423 
	// (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18 
	Date.prototype.Format = function (fmt) { //author: meizz 
	    var o = {
	        "M+": this.getMonth() + 1, //月份 
	        "d+": this.getDate(), //日 
	        "h+": this.getHours(), //小时 
	        "m+": this.getMinutes(), //分 
	        "s+": this.getSeconds(), //秒 
	        "q+": Math.floor((this.getMonth() + 3) / 3), //季度 
	        "S": this.getMilliseconds() //毫秒 
	    };
	    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
	    for (var k in o)
	    if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
	    return fmt;
	}
    
// 菜单控制
function menuControl( obj, type, recursion ){
    // 展开时对其他菜单项进行处理(暂不实现)
    // if( recursion ){}
    var _title = obj.find('a:eq(0)');
    _title.data('hasOpen', _title.data('hasOpen')?false:true );
    switch ( type )
    {
        case ('open'):
        
            _title.addClass('f-menu');
            obj.children('.submenu, .module').removeClass('none');
        break;
        case ('close'):
            _title.removeClass('f-menu');
            
            obj.children('.submenu, .module').addClass('none');
        
        break;
    }
    //if( obj.find("ul").is('ul') )menuControl( $("ul:eq(0) li:eq(0)", obj), type, true );
    if( $('ul li:eq(0) ul', obj).is('ul') )menuControl( $("ul:eq(0) li:eq(0)", obj), type, true );
}
//菜单增加tab栏目
function add(obj){
	var title = $(obj).html();
	var href = $(obj).attr("href");
	window.parent.addTab(title,href);
	return false;
}

function m_info(content){
	$.messager.alert('提示',content,'info');
}
function m_erro(content){
	$.messager.alert('错误',content,'error');
}
function m_ques(content){
	$.messager.alert('确认',content,'question');
}
function m_warn(content){
	$.messager.alert('警告',content,'warning');
}
/**
 * 根据主键删除数据
 * @param param
 */
function del(id){
	var param = encodeURIComponent(param);
	var obj = new Object();
	obj["id"] = id;
	$.messager.confirm('提示', '确定要删除吗?', function(r){
		if (r){
			 $.post(url+"/"+action+"/del/"+Math.random(),obj, function(data) {
				 if(data >0 ){ //影响行大于0则成功
					 m_info("删除成功");
				 }else{
					 m_erro("删除失败");
				 }
		     });
			 $("#submit_search").click();
		}
	});
	
}

//绑定搜索框和分页参数 
$(function(){  //初始化列表动作
	var obj = document.getElementById("submit_search");
	if (obj){  
		  $("#submit_search").click(function () {
		       $('#dg').datagrid({ queryParams: form2Json("searchform") });   //点击搜索
		  }); 
	} //如果是index页面  则进行页面数据展示控制
	var dg = document.getElementById("dg");
	if (dg){  
		  var pager = $('#dg').datagrid().datagrid('getPager');	// get the pager of datagrid
		  pager.pagination({
//			  pageSize: 20,//每页显示的记录条数，默认为10 
//		      pageList: [15,20,30,40,50],//可以设置每页记录条数的列表 
		      beforePageText: '第',//页数文本框前显示的汉字 
		      afterPageText: '页    共 {pages} 页', 
		      displayMsg: '当前显示 {from} - {to} 条记录   共 {total} 条记录',  
		  });			
	}
	
});

function view_user_info(user_id){
//		window.parent.addTab(user_id+"详细",url+"/UserInfo/view/"+user_id);
	
	 $.post(url+"/UserInfo/view/"+user_id, "json=", function(data) {
//		 var content = '				<div style="display: block; width: 600; left: 326px; top: 206px; z-index: 9001;" class="panel window"><div style="width: 488.4px;" class="panel-header panel-header-noborder window-header"><div style="" class="panel-title panel-with-icon">用户信息查看</div><div class="panel-icon icon-save"></div></div><div id="w" class="easyui-window panel-body panel-body-noborder window-body" title="" data-options="iconCls:\'icon-save\'" style="padding: 10px; width: 466.8px; height: px;">					'+data+'				</div></div><div style="display: block; left: 326px; top: 206px; z-index: 9000; width: 500px; height: 200px;" class="window-shadow"></div>'; 
//			$(document.body).append(content);	
		 $.messager.alert('个人信息查看',data,'info'); 
		 $(".messager-body").css("width","600px");
		 $(".messager-window").css("width","600px");
		 $(".window-header").css("width","600px");
		 $(".messager-window").css("left","200px");
		 $(".window-shadow").css("left","200px");
		 $(".messager-button").css("display","none");
		 $(".window-shadow").css("height","100px");
     });

  }
/**
 * 用于datagrid加载完成之后处理
 * @param data
 */
function loaddgSuc(data){
	if(data.total === 0 ){
		$("#msg").html("没有相关记录");
	}else{
		$("#msg").html("");
	}
}
