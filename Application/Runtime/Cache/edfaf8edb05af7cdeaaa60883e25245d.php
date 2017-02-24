<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>挂号订单详情</title>
    <link rel="stylesheet" type="text/css" href="__PUBLIC__/easyui/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="__PUBLIC__/easyui/themes/icon.css">
<script type="text/javascript" src="__PUBLIC__/easyui/jquery.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/easyui/locale/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/easyui_common.js"></script>

    <style>
        .mass {
            width: 47%;
            height: auto;
            float: left;
            padding-top: 3px;
            padding-bottom: 3px;
            margin: 0 0 0 5px;
        }

        .play {
            width: 45%;
            height: auto;
            float: left;
            padding-top: 3px;
            padding-bottom: 3px;
            margin: 0 3px 0 5px;

        }

        .cost {
            float: right;
            width: 100%;
            text-align: right;
            padding-top: 10px;
            padding-right: 10px;
            border-top: 1px dashed #4B849E;
        }

        th {
            padding-top: 10px;
        }

        td {
            padding-top: 10px;
        }

    </style>

</head>
<body>
<div class="easyui-panel" title="<?php echo ($title); ?>" style="width:100%;">

    <div class="mass">
        <table>
            <tr>
                <th>挂号信息</th>
            </tr>
            <tr>
                <td>订单号：</td>
                <td><?php echo ($order["order_id"]); ?></td>
            </tr>
            <tr>
                <td>下单时间：</td>
                <td><?php echo ($order["insert_dt"]); ?></td>
            </tr>
            <tr>
                <td>挂号医院：</td>
                <td><?php echo ($order["hospital"]); ?></td>
            </tr>
            <tr>
                <td>挂号科室：</td>
                <td><?php echo ($order["department"]); ?></td>
            </tr>
            <tr>
                <td>挂号医生：</td>
                <td><?php echo ($order["doctor"]); ?></td>
            </tr>
            <tr>
                <td>预约时间：</td>
                <td><?php echo ($order["shift_date"]); ?></td>
            </tr>
            <tr>
                <td>就诊人：</td>
                <td><?php echo ($order["patient_name"]); ?></td>
            </tr>
            <tr>
                <td>身份证：</td>
                <td><?php echo ($order["id_card"]); ?></td>
            </tr>
            <tr>
                <td>手机号：</td>
                <td><?php echo ($order["patient_phone"]); ?></td>
            </tr>
        </table>
    </div>

    <div class="play">
        <table>
            <tr>
                <th>支付信息</th>
            </tr>
            <tr>
                <td>支付方式：</td>
                <td><?php echo ($pay["payment"]); ?></td>
            </tr>
            <tr>
                <td>订单状态：</td>
                <td><?php echo ($pay["pay_state"]); ?></td>
            </tr>
            <tr>
                <td>支付时间：</td>
                <td><?php echo ($pay["pay_dt"]); ?></td>
            </tr>
            <tr>
                <td>支付渠道：</td>
                <td><?php echo ($pay["pay_type"]); ?></td>
            </tr>
            <tr>
                <td>支付流水号：</td>
                <td><?php echo ($pay["pay_no"]); ?></td>
            </tr>
            <tr>
                <th>退款信息</th>
            </tr>
            <tr>
                <td>申请退款时间：</td>
                <td><?php echo ($refund["create_dt"]); ?></td>
            </tr>
            <tr>
                <td>审批退款记录：</td>
                <td><?php echo ($refund["busi_approve_dt"]); ?></td>
            </tr>
            <tr>
                <td></td>
                <td><?php echo ($refund["busi_approve_reason"]); ?></td>
            </tr>
            <tr>
                <td></td>
                <td><?php echo ($refund["fina_approve_dt"]); ?></td>
            </tr>
            <tr>
                <td></td>
                <td><?php echo ($refund["fina_approve_reason"]); ?></td>
            </tr>
            <tr>
                <td>盈余：</td>
                <td><?php echo ($pay["surplus"]); ?></td>
            </tr>
            <tr>
                <td></td>
            </tr>
        </table>
    </div>

    <div class="cost">
        <p><span class="w_lb">挂号费：</span><?php echo ($order["reg_fee"]); ?></p>
        <p><span class="w_lb">检查费：</span><?php echo ($cost["check"]); ?></p>
        <p><span class="w_lb">病历本：</span><?php echo ($order["medical_card_price"]); ?></p>
        <p><span class="w_lb">就诊卡：</span><?php echo ($order["medical_book_price"]); ?></p>
        <p><span class="w_lb"><b>订单费：</b></span><?php echo ($cost["total"]); ?></p>
    </div>
</div>
</div>
</body>
</html>