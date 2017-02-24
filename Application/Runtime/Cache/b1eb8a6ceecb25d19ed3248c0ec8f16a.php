<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>订单报表</title>
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
        .calendar-disabled{background: #eeeeee;color: #666666;border-radius: 0px}
    </style>
</head>
<body>
<div id="tb" style="padding:5px;height:auto;">
    <div style="margin: 10px 0 10px 10px" >
        <!-- form表单 指定id和name之后会自动带到服务器 -->
        <form name="searchform" method="get" action="" id ="searchform" style="height:auto;margin-bottom: 20px; line-height: 40px">

            <label for="time_frame">时间范围：</label>
            <select class="easyui-combobox" id ="time_frame" name="time_frame" style="width:140px;height:32px;">
                <option value="1">下单时间</option>
                <option value="2">预约时间</option>
                <option value="3" selected="selected">支付时间</option>
                <option value="4">申请退款时间</option>
                <option value="5">审批退款时间</option>
            </select>
            <input type="datetime" value="<?php echo ($timeFor); ?>" name="time_for" id="time_for" style="width:140px;height:32px">
            <label>至</label>
            <input style="width:140px;height:32px" value="<?php echo ($timeTo); ?>" type="datetime" name="time_to" id="time_to">
            <br/>
            <label for="pay_type">支付渠道：</label>
            <select class="easyui-combobox" id ="pay_type" name="pay_type" style="width:140px;height:32px;">
                <option value="">全部</option>
                <?php if(is_array($payType)): $i = 0; $__LIST__ = $payType;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["pay_type"]); ?>"><?php echo ($vo["pay_type_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
            </select>
            <label for="pay_state">&nbsp;&nbsp;订单状态：</label>
            <select class="easyui-combobox" id ="pay_state" name="pay_state" style="width:140px;height:32px;">
                <option value="">全部</option>
                <option value="1,9">&nbsp;</option>
                <option value="2">待支付</option>
                <option value="4">支付失败</option>
                <option value="0" selected="selected">已支付</option>
                <option value="10">待业务审批</option>
                <option value="11">待财务审批</option>
                <option value="12">业务拒绝退款</option>
                <option value="13">财务拒绝退款</option>
                <option value="14">退款中</option>
                <option value="6">退款成功</option>
                <option value="7">退款失败</option>
            </select>
            <a href="javascript:void(0);" id="submit_search" class="easyui-linkbutton" iconCls="icon-search"  style="width:60px;height:32px">查询</a>&nbsp;&nbsp;
            <a href="javascript:void(0);" id="advanced_filter" onclick="advancedFilter()">高级筛选</a>
            <a href="javascript:void(0);" id="cl_advanced_filter" style="display: none" onclick="clAdvancedFilter()">收起高级筛选</a>
            <div class="advanced_filter">
                <input type="hidden" name="advanced" id="advanced" value="0">
                <label for="hospital">挂号医院：</label>
                <select class="easyui-combobox" id ="hospital" name="hospital" style="width:140px;height:32px;">
                    <option value="">全部</option>
                    <?php if(is_array($hospital)): $i = 0; $__LIST__ = $hospital;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["hosl_name"]); ?>"><?php echo ($vo["hosl_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
                <label for="payment">&nbsp;&nbsp;支付方式：</label>
                <select class="easyui-combobox" id ="payment" name="payment" style="width:140px;height:32px;">
                    <option value="0">全部</option>
                    <option value="1" selected="selected">线上支付</option>
                    <option value="2">线下支付</option>
                </select>
                <br/>
                <label for="order_id">订单搜索：</label>
                <input class="easyui-textbox" id="order_id" name="order_id" data-options="prompt:'请输入订单号'" style="width:180px;height:32px"/>
            </div>
        </form>
    </div>
</div>
<table id="dg"  style="width:100%; height: 600px;"  title="查询结果"
       data-options="rownumbers:false,
			singleSelect:true,
			pagination:true,
			ctrlSelect:true,
			fitColumns:true,
			loadMsg:'正在加载,请等待....',
			url:'__URL__/getOrderReportList',
			method:'post',
			toolbar:'#tb',
			pageSize: 30,
			pageList: [30,50,100],
			onLoadSuccess:total
			">
    <thead>
    <tr>
        <th data-options="field:'order_id',align:'center',width:'20px'">订单号</th>
        <th data-options="field:'insert_dt',align:'center',width:'20px'">下单时间</th>
        <th data-options="field:'hospital_name',align:'center',width:'20px'">挂号医院</th>
        <th data-options="field:'reg_fee',align:'center',width:'10px'">挂号费(元/￥)</th>
        <th data-options="field:'shift_date',align:'center',width:'15px'">预约时间</th>
        <th data-options="field:'payment',align:'center',width:'10px'">支付方式</th>
        <th data-options="field:'pay_state_name',align:'center',width:'10px'">订单状态</th>
        <th data-options="field:'pay_dt',align:'center',width:'20px'">支付时间</th>
        <th data-options="field:'pay_type',align:'center',width:'10px'">支付渠道</th>
        <th data-options="field:'refund_create_dt',align:'center',width:'20px'">申请退款时间</th>
        <th data-options="field:'refund_approve_dt',align:'center',width:'20px'">审批退款时间</th>
        <th data-options="field:'surplus',align:'center',width:'10px'">盈余(元/￥)</th>
        <th data-options="field:'op',formatter:formatOperation,align:'center',width:'25px'">操作</th>
    </tr>
    </thead>
</table>
<div style="padding:5px;height:auto;background: #F4F4F4;border: 1px solid #95B8E7;border-top: none;height: 32px;line-height: 32px;">
    <span>线上已支付统计：</span>
    <span>订单<strong id="total_num">0</strong>笔，</span>
    <span>挂号费 <strong style="color: red;" id="total_reg_fee">0.00</strong>元，</span>
    <span>挂号费退款 <strong style="color: red;" id="total_refund_fee">0.00</strong>元，</span>
    <span>盈余 <strong id="total_surplus" style="color: red;">0.00</strong>元</span>
    <a href="javascript:void(0);" id="submit_excel" onclick="derivedExcel()" class="easyui-linkbutton"  style="width:100px;height:32px;float: right;">导出Excel</a>
</div>
<script type="text/javascript">
    var height = document.documentElement.clientHeight;
    height = height - 60;
    $("#dg").css("height",height+"px");
    $(function(){
        $('#time_for').datebox().datebox('calendar').calendar({
            validator: function(date){
                var now = new Date();
                var d1 = new Date(now.getFullYear()-1, now.getMonth(), now.getDate()-1);
                return d1 <= date;
            }
        });
        $('#time_to').datebox().datebox('calendar').calendar({
            validator: function(date){
                var now = new Date();
                var d1 = new Date(now.getFullYear()-1, now.getMonth(), now.getDate()-1);
                return d1 <= date;
            }
        });
        $(".advanced_filter").hide();
    });
    function advancedFilter(){
        $("#advanced_filter").hide();
        $("#cl_advanced_filter").show();
        $(".advanced_filter").show();
        $("#advanced").val(1);
    }
    function clAdvancedFilter(){
        $("#advanced_filter").show();
        $("#cl_advanced_filter").hide();
        $(".advanced_filter").hide();
        $("#advanced").val(0);
    }
    function orderInfo(order_id){
        layer.open({
            type: 2,
            title: '订单详情',
            fix: false,
            shadeClose: true,
            maxmin: false,
            area: ['700px', '100%'],
            content: ['__URL__/orderInfo/?order_id='+order_id,'yes'],
            end: function(){}
        });
    }
    function total() {
        var rows = $('#dg').datagrid('getRows');
        var num = rows.length;
        var totalRefundFee = "0.00";
        var totalRegisterFee = "0.00";
        var totalSurplus = "0.00";
        if(num > 1){
            var total = num - 1;
            $("#total_num").html(rows[total].total_num);
            totalRefundFee = rows[total].refund_reg_fee;
            totalRefundFee = Number(totalRefundFee).toFixed(2);
            totalRegisterFee = Number(rows[total].reg_fee).toFixed(2);
            totalSurplus = Number(rows[total].surplus).toFixed(2);
        }else {
            $("#total_num").html(0);
        }
        $("#total_reg_fee").html(totalRegisterFee);
        $("#total_refund_fee").html(totalRefundFee);
        $("#total_surplus").html(totalSurplus);
        if(num > 0) {
            $('#dg').datagrid('deleteRow', num - 1);
        }
    }
    function formatOperation(val,row) {
        var permissions = <?php echo ($permissions); ?>;
        var orderId = row.order_id;
        val = "<a href='javascript:void(0);' onclick='orderInfo(\""+orderId+"\")'>订单详情</a>";
        if(permissions.apply_for_refund == true && row.pay_state == "0" && row.pay_type_id != "5"){
            val = val + "&nbsp;<a href='javascript:void(0);' onclick='applyForRefund(\""+orderId+"\")'>申请退款</a>";
        }
        return val;
    }
    //请求退款
    function applyForRefund(orderId) {
        layer.open({
            type: 2,
            title: '申请退款',
            fix: false,
            shadeClose: false,
            maxmin: false,
            area: ['500px', '400px'],
            content: ['__URL__/applyForRefund/?order_id='+orderId,'no'],
            end: function(){}
        });
    }
    //导出excel
    function derivedExcel() {
        var json = form2Json("searchform");
        var url = '__URL__/orderReportDerivedExcel?';
        for (var x in json) {
            url = url + "&" + x + "=" + json[x];
        }
        location.href = url;
    }
</script>
</body>
</html>