<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>亲情账号</title>
    <link rel="stylesheet" type="text/css" href="__PUBLIC__/easyui/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="__PUBLIC__/easyui/themes/icon.css">
<script type="text/javascript" src="__PUBLIC__/easyui/jquery.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/easyui/locale/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/easyui_common.js"></script>
<script type="text/javascript" src="__PUBLIC__/layer/layer.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/json2.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/common.js"></script>
    <style>
        .nav_tabs {
            width: 100%;
            height: 32px;
            border-bottom: 1px solid #95B8E7;
            margin: 0px;
            padding: 0px;
            line-height: 32px;
        }

        .nav_tabs li {
            float: left;
            list-style-type: none;
            height: 32px;
            padding: 0 20px;
            border-top: 1px solid #95B8E7;
            border-right: 1px solid #95B8E7;
            border-bottom: none;
            cursor: pointer
        }

        .nav_tabs li.active {
            background: #95B8E7;
            font-weight: bold;
        }

        #win .box {
            height: auto;
            background-color: #fff;
            margin: 10px;
            padding: 10px;
            border: 1px solid #4B849E;
            font-size: 14px;
            line-height: 24px;
        }

        #win .box p {
            margin: 0;
        }

        #win .box p.title {
            font-weight: bold;
            font-size: 16px;
            line-height: 32px;
        }

        #win .box p span.w_lb {
            padding-right: 2px;
        }

        #win .box p span.w_val {
            padding-left: 4px;
        }

        #win .box .left {
            width: 50%;
            float: left;
            height: 100%;
            margin: 0;
        }

        #win .box_one .left {
            height: auto;
        }

        #win .box .left .l_nav {
            line-height: 24px;
            font-size: 14px;
            padding: 0 1%;
        }

        #win .box .left .l_box {
            width: 98%;
            height: 210px;
            border: 1px solid #4B849E;
        }

        #win .box .my_la1 {
            line-height: 32px;
        }

        #win .box .my_box {
            margin-top: 8px;
        }

    </style>
    <script type="text/javascript" language="javascript">
        var idTmr;
        function getExplorer() {

            //window.navigator.userAgent属性包含浏览器类型、版本、操作系统类型、浏览器引擎类型等信息，通过这个属性来判断浏览器类型
            var explorer = window.navigator.userAgent;
            //alert(explorer);
            // Mozilla/5.0 (Windows NT 6.1; WOW64; rv:48.0) Gecko/20100101 Firefox/48.0

            //ie 如果MSIE出现在explorer中，则是ie浏览器
            if (explorer.indexOf("MSIE") >= 0) 
            //firefox
            else if (explorer.indexOf("Firefox") >= 0) {
                return 'Firefox';
            }
            //Chrome
            else if (explorer.indexOf("Chrome") >= 0) {
                return 'Chrome';
            }
            //Opera
            else if (explorer.indexOf("Opera") >= 0) {
                return 'Opera';
            }
            //Safari
            else if (explorer.indexOf("Safari") >= 0) {
                return 'Safari';
            }
        }
        function method1(tableid)  catch (e) { //e是异常now出来的对象，里面有操作异常信息的方法//处理异常的语句
                    print("Nested catch caught " + e);
                } finally 

            }
            else {
                tableToExcel('ta')
            }
        }
        function Cleanup() {
            window.clearInterval(idTmr);  //取消setinterval()所设置的时间
            CollectGarbage();  //释放内存
        }
        // 自执行的匿名函数，这段代码被载入时候自动执行。
        var tableToExcel = (function () {
            var uri = 'data:application/vnd.ms-excel;base64,',   //用js base64处理，好使浏览器将其中的数据当做Excel来处理
            //定义一个命名空间
                    template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
                    base64 = function (s) ,
                    format = function (s, c) {
                        return s.replace(/{(w+)}/g,  //字符替换
                                function (m, p) {
                                    return c[p];
                                })
                    }
            return function (table, name) {
                if (!table.nodeType) table = document.getElementById(table)
                var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
                window.location.href = uri + base64(format(template, ctx))
            }
        })()
    </script>
</head>
<body>
<div id="tb" style="padding:5px 0;height:auto;">
    <div style="margin: 10px 0 10px 10px">
        <!-- form表单 指定id和name之后会自动带到服务器 -->
        <form name="searchform" method="get" action="" id="searchform" style="height:auto;margin-bottom: 20px;">
            <label for="time_for">时间范围:</label>
            <select class="easyui-combobox" id="time_frame" name="time_frame" style="width:140px;height:32px;">
                <option value="1" selected="selected">提交审核时间</option>
                <option value="2">审核成功时间</option>
                <option value="3">审核失败时间</option>
            </select>
            <input type="datetime" value="<?php echo ($timeFor); ?>" name="time_for" id="time_for" style="width:140px;height:32px">
            <label>至</label>
            <input style="width:140px;height:32px" value="<?php echo ($timeTo); ?>" type="datetime" name="time_to" id="time_to">
            <a href="javascript:void(0);" id="submit_search" class="easyui-linkbutton" iconCls="icon-search"
               style="width:60px;height:32px">查询</a>
            <br/>
            <br/>
            <input type="hidden" name="auth_status" value="2">
        </form>
    </div>
    <ul class="nav_tabs">
        <li data-auth-status="2" class="active">待人工审核</li>
        <li data-auth-status="4" class="act">人工审核失败</li>
        <li data-auth-status="6" class="act">审核成功</li>
        <li data-auth-status="7" class="act">审核失败</li>
    </ul>
</div>
<table id="dg" style="width:100%; min-height: 600px" title="查询结果"
       data-options="rownumbers:false,
         singleSelect:true,
         fitColumn:false,
         pagination:true,
         loadMsg:'正在加载,请等待...',
         url:'__URL__/getList',
         method:'post',
         toolbar:'#tb',
         pageSize: 30,
         pageList: [30,50,100],
         ">
    <!--固定某字段-->
    <thead data-options="frozen:true">
    <tr>
        <th data-options="field:'auth_status_name',align:'center',width:'100px'">审核状态</th>
        <th data-options="field:'id',align:'center',width:'130px'">编号</th>
    </tr>
    </thead>
    <thead>
    <tr>
        <th data-options="field:'patient_name',align:'center',width:'130px'">姓名</th>
        <th data-options="field:'nation',align:'center',width:'130px'">民族</th>
        <th data-options="field:'patient_id_no',align:'center',width:'130px'">身份证号</th>
        <th data-options="field:'patient_sex',align:'center',width:'130px'">性别</th>
        <th data-options="field:'patient_age',formatter:age,align:'center',width:'150px'">年龄</th>
        <th data-options="field:'patient_phone',align:'center',width:'150px'">手机号</th>
        <th data-options="field:'patient_addr',align:'center',width:'150px'">居住地址</th>
        <th data-options="field:'remarks',formatter:remark,align:'center',width:'150px'">备注</th>
        <th class="i" data-options="field:'operation',formatter:operation,align:'center',width:'150px'">操作</th>
    </tr>
    </thead>
</table>
<div id="win" title="审核" style="width: 700px;height: 600px;display: none;margin: 0px auto">
    <div id="w" class="box box_one">
        <p class="title">用户信息</p>

        <div class="left">
            <p><span class="w_lb">用户姓名</span>:<span id="w_name" class="w_val"></span></p>

            <p><span class="w_lb">用户民族</span>:<span id="w_nation" class="w_val"></span></p>

            <p><span class="w_lb">身份证号</span>:<span id="w_id_no" class="w_val"></span></p>
        </div>
        <div class="left">
            <p><span class="w_lb">用户性别</span>:<span id="w_sex" class="w_val"></span></p>

            <p><span class="w_lb">用户年龄</span>:<span id="w_age" class="w_val"></span></p>

            <p><span class="w_lb">手机号码</span>:<span id="w_phone" class="w_val"></span></p>
        </div>
        <p><span class="w_lb">居住地址</span>:<span id="w_address" class="w_val"></span></p>
    </div>
    <div id="w1" class="box" style="height: 270px">
        <p class="title">证件照片</p>

        <div class="left">
            <p class="l_nav">正面：</p>

            <div class="l_box"></div>
        </div>

        <div class="left">
            <p class="l_nav">反面：</p>

            <div class="l_box"></div>
        </div>
    </div>

    <div id="img" align="center" style="display: none;position:absolute;left:50%;right:50%;">
        <img src="__PUBLIC__/images/loading.gif" alt="正在加载……">
    </div>

    <div class="box" style="border: medium none; margin-top: 0px; margin-bottom: 0px; padding: 0px; padding-top: 0px;">
        <p class="title">备注：</p>
        <textarea style="width: 100%;height: 50px" id="remark"></textarea>

        <div class="my_la1">
            <label><input name="auth_status" type="radio" value="3"/>认证成功 </label>
            <label><input name="auth_status" type="radio" value="4"/>认证失败 </label>
            <input type="hidden" name="id">
        </div>
        <p class="title c_reason">请选择失败原因(*必填)</p>
        <select name="reason" id="reason" class="c_reason" style="width:100%;height:32px;">
            <option value="请选择">请选择</option>
            <option value="证件照不清晰">证件照不清晰</option>
            <option value="证件信息与本人信息不相符">证件信息与本人信息不相符</option>
            <option value="证件已过期">证件已过期</option>
        </select>

        <div class="my_box">
            <a href="javascript:void(0);" id="b_remark_no" class="easyui-linkbutton"
               style="width:60px;height:32px;float: right">取消</a>
            <a href="javascript:void(0);" id="b_remark_save" class="easyui-linkbutton"
               style="width:60px;height:32px;float: right;margin-right: 20px">确定</a>
        </div>
    </div>
</div>

<table id="ta">
    <tr>
        <td>id</td>
        <td>admin</td>
        <td>23</td>
        <td>程序员</td>
        <td>天津</td>
        <td>admin@kali.com</td>
    </tr>
    <tr>
        <td>1</td>
        <td>guest</td>
        <td>23</td>
        <td>测试员</td>
        <td>北京</td>
        <td>guest@kali.com</td>
    </tr>
</table>
<input id="Button1" type="button" value="导出EXCEL"
       onclick="javascript:method1('ta')"/>



<script type="text/javascript">
    $('#dg').datagrid({queryParams: form2Json("searchform")});
    $(function () {
        $('#time_for').datebox().datebox('calendar').calendar({
            validator: function (date) {
                var now = new Date();
                var d1 = new Date(now.getFullYear() - 1, now.getMonth(), now.getDate() - 1);
                return d1 <= date;
            }
        });
        $('#time_to').datebox().datebox('calendar').calendar({
            validator: function (date) {
                var now = new Date();
                var d1 = new Date(now.getFullYear() - 1, now.getMonth(), now.getDate() - 1);
                return d1 <= date;
            }
        });

        $(".nav_tabs").find("li").click(function () {
            $(".nav_tabs").find("li").removeClass("active");
            $(this).addClass("active");
            var auth_status = $(this).attr("data-auth-status");
            $("input[name='auth_status']").val(auth_status);
            $('#dg').datagrid({queryParams: form2Json("searchform")});
        });
        $("#b_remark_no").click(function () {
            layer.closeAll();
        });

        $("input[name='auth_status']").change(function () {
            var auth_status = $("input[name='auth_status']:checked").val();
            auth_status = Number(auth_status);
            if (auth_status == 4) {
                $(".c_reason").show();
            } else {
                $(".c_reason").hide();
            }
        });
        $("#b_remark_save").click(function () {
            var auth_status = $("input[name='auth_status']:checked").val();
            auth_status = Number(auth_status);
            if (auth_status != 3 && auth_status != 4) {
                layer.msg("请选择认证是否通过");
                return;
            }
            var reason = $("#reason option:selected").text();
            var remark = $("#remark").val();
            if (auth_status == 4) {
                if (reason == "请选择") {
                    layer.msg("请选择审批失败的原因！");
                    return;
                }
                if (remark == "") {
                    layer.msg("请填写备注原因！");
                    return;
                }
            }
            var id = $("input[name='id']").val();
            layer.msg("确定提交？", {
                time: 3000, //3s后自动关闭
                btn: ['确定', '取消'],
                yes: function () {
                    $("#img").show();
                    $.ajax({
                        url: '__URL__/audit',
                        type: 'post',
                        data: {'remarks': remark, 'reason': reason, 'id': id, 'auth_status': auth_status},
                        cache: false,
                        dataType: 'json',
                        success: function (data) {
                            if (data.code == 0) {
                                location.replace(location.href);
                                layer.closeAll();
                            } else {
                                location.replace(location.href);
                                layer.closeAll();
                            }
                        },
                        error: function () {
                            layer.msg("操作错误");
                        }
                    });
                }
            })
        });
    });
    function age(val, row) {
        var birthday = row.patient_birthday;
        var now = new Date();
        var year = now.getFullYear();
        var arr = birthday.split("-");
        if (arr.length == 3) {
            var my_age = year - arr[0];
            if (my_age == year) my_age = 0;
            return my_age;
        } else {
            return 0;
        }
    }

    function remark(val) {
        val = val == null ? "" : val;
        return "<a title='" + val + "'>" + val + "</a>";
    }

    function operation(val, row) {
        var auth_status = Number(row.auth_status);
        if (auth_status == 2 || auth_status == 3) {
            return "<a href='javascript:void(0);' onclick='audit(" + row.id + ",\"" + row.patient_birthday + "\",\"" + row.patient_name + "\",\"" + row.nation + "\",\"" + row.id_photo + "\",\"" + row.patient_id_no + "\",\"" + row.patient_sex + "\",\"" + row.patient_phone + "\",\"" + row.patient_addr + "\",\"" + row.remarks + "\")'>认证操作</a>"
        }
        if (auth_status == 5) {
            return "<a title='" + row.opt_admin + "'>操作人员：" + row.opt_admin + "<br/>操作时间：" + row.update_dt + "</a>";
        }
    }

    function audit(id, patient_birthday, name, nation, idPhoto, patient_id_no, patient_sex, patient_phone, patient_addr, remarks) {
        //字段显示
        remarks = remarks == null ? "" : remarks;
        $("#w_name").html(name);
        $("#w_nation").html(nation);
        $("#w_id_no").html(patient_id_no);
        $("#w_sex").html(patient_sex);
        $("#w_phone").html(patient_phone);
        $("#w_address").html(patient_addr);
        $("#remarks").val(remarks);
        $("input[name='id']").val(id);
        var birthday = patient_birthday;
        var now = new Date();
        var year = now.getFullYear();
        var arr = birthday.split("-");
        if (arr.length == 3) {
            var my_age = year - arr[0];
            if (my_age == year) my_age = 0;
            $("#w_age").html(my_age);
        }
        var url = "<?php echo ($idCardUrl); ?>";
        var idPhotoArr = idPhoto.split("|");
        $("#win .box .left .l_box").eq(0).html("<img src='" + url + idPhotoArr[0] + "' style='width: 100%;height: 100%'>");
        $("#win .box .left .l_box").eq(1).html("<img src='" + url + idPhotoArr[1] + "' style='width: 100%;height: 100%'>")
        //弹出新窗口
        bombBoxDiv($("#win"), "审核", "750px", "95%");
    }

</script>
</body>
</html>