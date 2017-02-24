<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo ($title); ?></title>
    <link rel="stylesheet" type="text/css" href="__PUBLIC__/easyui/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="__PUBLIC__/easyui/themes/icon.css">
<script type="text/javascript" src="__PUBLIC__/easyui/jquery.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/easyui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/easyui/locale/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/easyui_common.js"></script>

    <style>
        #tb22_a{
            background:#F84E3A;position:relative;z-index:10;margin:300px 0px 0 700px;height:50px;width:50px;color:#fff;border:0;border-radius: 25px;opacity: 0.5;display: block;line-height:50px;text-align:center;text-decoration: none;
        }
        #tb22_a:hover{
            background:#64A9CF;
            opacity: 1;
        }

    </style>
</head>
<body>
<div class="easyui-panel" title="<?php echo ($title); ?>" style="width:100%;">
    <div style="padding:10px 60px 20px 60px">
        <form id="ff" method="post" action="__URL__/centerEdit">
            <table cellpadding="5">
                <tr>
                    <td align="right">标题：</td>
                    <td>
                        <input class="easyui-textbox" id="module_name" value="<?php echo ($detail["module_name"]); ?>" name="module_name" data-options="required:true,prompt:'请输入Banner标题',missingMessage:'标题不能为空'" style="width:200px;height:32px"/>
                        <input type="hidden" id="module_id" name="module_id" value="<?php echo ($detail["module_id"]); ?>">
                    </td>
                </tr>
                <tr style="display: none;">
                    <td align="right">模块链接：</td>
                    <td>
                        <input class="easyui-textbox" id="module_url" value="<?php echo ($detail["module_url"]); ?>" name="module_url" data-options="prompt:'请输入模块链接'" style="width:200px;height:32px"/>
                    </td>
                </tr>
                <tr>
                    <td align="right">图片：</td>
                    <td>
                        <div id="pic" >
                            <img src='<?php echo ($detail["pic_name"]); ?>'  height='200px' />
                        </div>
                        <input type="file" id="file" name="file" />
                        <input type="hidden" id="pic_name" name="pic_name" value="<?php echo ($detail["pic_name"]); ?>" />
                        <a href="#" class="easyui-linkbutton" onclick="ajaxFileUpload('file');" >上传图片</a>
                        <a href="#" class="easyui-linkbutton" onclick="delBannerImg()" data-options="iconCls:'icon-clear'">删除banner图片</a>

                    </td>
                </tr>
                <tr>
                    <td>跳转链接：</td>
                    <td>
                        <input class="easyui-textbox" id="pic_url" value="<?php echo ($detail["pic_url"]); ?>" name="pic_url" data-options="validType:'url',multiline:true,prompt:'请输入跳转链接',missingMessage:'跳转地址不能为空'" style="width:200px;height:32px"/><span style="color: red;margin-left:10px;">注：地址格式（"http://baidu.com"）</span>
                    </td>
                </tr>
                <tr>
                    <td>列表配置：</td>
                    <td>
                        <input type="radio" name="radio_sel" checked="checked" onclick="online_select('1')">指定医生　
                        <?php if($detail['doc_id']=='0'){ ?>
                            <input type="radio" name="radio_sel" checked="checked" onclick="online_select('2')">在线医生
                        <?php }else{ ?>
                            <input type="radio" name="radio_sel" onclick="online_select('2')">在线医生
                        <?php } ?>
                    </td>
                </tr>
                <tr id="online_id">
                    <td>医生ID：</td>
                    <td>

                        <input class="" id="doc_id" value="<?php echo ($detail["doc_id"]); ?>" readonly="readonly" name="doc_id"  style="width:350px;height:28px">

                        <a href="javascript:void(0);" class="easyui-linkbutton" id="selected" iconCls="icon-add" style="width:100px;height:32px" onclick="$('#w').window('open')">选择医生</a>
                        <a href="javascript:void(0);" class="easyui-linkbutton" iconCls="icon-clear" style="width:100px;height:32px" onclick="clearDocId()">清空选择</a>
                        <BR><BR><span>注:多个医生之间“,”隔开，如：XXXXX,XXXXX</span>
                        <span id="doc_flag_data" style="display: none"><?php echo ($detail["doc_id"]); ?></span>
                    </td>
                </tr>
                <tr>
                    <td style="">&nbsp;</td>
                    <td style="color: red;">
                        <br>
                        <br>
                        <br>
                        注意：若无启用模块，则为设为启用状态，否则为停用状态！
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <div style="padding:5px 0">
                            <a href="javascript:void(0);" class="easyui-linkbutton"  onclick="submitForm()" data-options="iconCls:'icon-save'" style="width:100px;height:32px">保存</a>
                            <a href="javascript:void(0);" class="easyui-linkbutton" id="return" iconCls="icon-undo" style="width:100px;height:32px" onclick="javascript:window.location.href='__URL__/center'">返回</a>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
<div id="w" class="easyui-window" title="选择在线医生" data-options="modal:true,closed:true,iconCls:'icon-save',buttons:'#bb22'" style="width:800px;height:500px;padding:10px;">
    <table id="dg"  style="width:97%;"  title="查询结果"
           data-options="rownumbers:false,
			singleSelect:true,
			title:false,
			pagination:true,
			ctrlSelect:true,
			fitColumns:true,
			loadMsg:'正在加载,请等待....',
			url:'__URL__/getDoctorList',
			method:'post',
			fit:true,
			toolbar:'#tb22',

			">
        <thead id="dg_list">
        <tr>
            <th data-options="field:'id',align:'center',width:'3px'">选择</th>
            <th data-options="field:'doc_id',align:'center',width:'5px'">医生ID</th>
            <th data-options="field:'doc_name',align:'center',width:'6px'">医生名称</th>
            <th data-options="field:'hospital',align:'center',width:'10px'">所属医院</th>
            <th data-options="field:'department',align:'center',width:'10px'">所属科室</th>
        </tr>
        </thead>
    </table>

    <div>


    </div>

</div>

<div id="tb22" style="height:40px;">
    <span style="color:red;line-height:40px;margin-left:5px;">注：确定的选择结果只限于当前页</span>
    <a href="#" id="tb22_a" class="" onclick="getSelect();" style="">确定</a>
</div>










<script type="text/javascript" src="__PUBLIC__/js/ajaxfileupload.js"></script>
<script type="text/javascript">
    if($("#doc_id").val()==''){
        $("#doc_id").val("0");
    }
    function ajaxFileUpload(file_id){
        var file_name = $("#"+file_id).val();
        if(file_name == "" ){
            $.messager.alert('提示','请选择图片!');
            return false;
        }
        var action_url = '<?php echo ($upload_addr); ?>';
        $.ajaxFileUpload
        (
                {
                    url: action_url, //用于文件上传的服务器端请求地址
                    type: 'post',
                    data: { }, //此参数非常严谨，写错一个引号都不行
                    secureuri: false, //一般设置为false
                    fileElementId: file_id, //文件上传空间的id属性  <input type="file" id="file" name="file" />
                    dataType: 'text', //返回值类型 一般设置为json
                    success: function (data, status)  //服务器成功响应处理函数
                    {
                        data = data.replace("<pre>","");
                        data = data.replace("</pre>","");
                        var json = JSON.parse(data);
                        if(json.code == 0){
                            $.messager.alert('提示',"上传成功");
                            $("#device_icon").val(json.file_name);
                            var img_html = "<img src='<?php echo ($down_addr); ?>"+json.file_name+"/"+Math.random()+"'  height='150px' />";
                            $("#pic_name").val("<?php echo ($down_addr); ?>"+json.file_name+"/"+Math.random());
                            $("#pic").html(img_html);
                        }else{
                            $.messager.alert('提示',"上传失败");
                        }
                    },
                    error: function (data, status, e)//服务器响应失败处理函数
                    {
                        alert(e);
                    }
                }
        );
        return false;
    }
    function submitForm(){
        $('#ff').form('submit',{
            onSubmit:function(){

                var form_valid = $(this).form('enableValidation').form('validate');
                if(!form_valid ){
                    return false;
                }

                var module_name = $("#module_name").val().trim();
                if(!module_name){
                    $.messager.alert('提示','请输入模块标题名称!');
                    return false;
                }
                var img = $("#pic_name").val().trim();

                //去掉上传图片必选项
                //if(!img){
                    //$.messager.alert('提示','请先上传图片!');
                    //return false;
                //}
                var doctor_id = $("#doc_id").val().trim();
                if(!doctor_id){
                    $.messager.alert('提示','请输入医生ID!');
                    return false;
                }
            },
            success:function(data){
                if (data >0) {
                    $.messager.alert("提示","操作成功");
                    window.location.href = "__URL__/center";
//		            	 setTimeout($("#return").click(),5000);
                }else{
                    $.messager.alert("提示","操作失败,数据无变化");
                }
            }
        });
    }
    var docIds = "<?php echo ($detail["doc_id"]); ?>";
    var docIdArr = new Array();
    docIdArr = docIds.split(",");
    $(function(){

    });

    function getSelect(){
        var sel=$("td[field='id']>div>input:checked");
        var len=sel.length;
        var str="";
        for(var i=0;i<len;i++){
            if(i==(parseInt(len)-1)){
                str=str+sel.eq(i).val();
            }else{
                str=str+sel.eq(i).val()+",";
            }
        }
        if(len>0){
            var old_doc_id=$("#doc_id").val();
            var old_arr=old_doc_id.split(",");
            var new_arr=str.split(',');
            var res_arr=mergeArray(old_arr, new_arr);
            if(old_doc_id==0 || old_doc_id==''){
                res_arr.shift();
            }
            str=res_arr.join(",");
            $("#555").val(str);
            $("#doc_flag_data").text(str);
            $("#doc_id").val(str);

        }
        $('#w').window('close', true);



    }

    //清空指定医生选择数据
    function clearDocId(){
        $("#doc_flag_data").text("0");
        $("#doc_id").val("0");
    }

    //合并两个数组并去重
    function mergeArray(arr1, arr2) {
        var _arr = [];
        for (var i = 0; i < arr1.length; i++) {
            _arr.push(arr1[i]);
        }
        var _dup;
        for (var i = 0; i < arr2.length; i++){
            _dup = false;
            for (var _i = 0; _i < arr1.length; _i++){
                if (arr2[i] === arr1[_i]){
                    _dup = true;
                    break;
                }
            }
            if (!_dup){
                _arr.push(arr2[i]);
            }
        }
        return _arr;
    }

    function online_select(id){

        if(id=='2'){
            //在线医生
            $("#online_id").hide();
            $("#doc_id").val("0");

        }
        if(id=='1'){
            //指定医生
            var flag_doc=$("#doc_flag_data").text();
            if(flag_doc==''){
                $("#doc_id").val("0");
            }else{
                $("#doc_id").val(flag_doc);
            }

            $("#online_id").show();
        }

    }

    var falg_now="<?php echo ($detail["doc_id"]); ?>";
    if(falg_now=='0'){
        $("#online_id").hide();
    }

    //删除banner图片
    function delBannerImg(){
        $.messager.confirm('确认','您确认想要删除记录吗？',function(r){
            if (r){
                $("#pic").html("");
                $("#pic_name").val("");
            }
        });


    }


</script>
</body>
</html>