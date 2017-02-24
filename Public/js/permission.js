
$(document).ready(function(){
	
		var admin =$('#session').val();
   	var rights=$('#rights_flag').val()
   
   $('input[type=checkbox]').each(function(){
		var field='img_'+$(this).attr('id');	//加载时展开复选框树形结构
		onClick(field);
	})
		
    	  
    $(".inputClass").live("click",function(){checkChange($(this).attr("id"));});
    $("img").live("click",function(){onClick($(this).attr("id"))});
    
    var peStr=$("#admin_level").val(); //加载时从查询结果集中取出用户已有权限并在相应复选框中选中
		if(peStr!=""){
   		
			
	   var peArray=peStr.split(" ");
	  
	   for(i=0;i<peArray.length;i++){
	   	
	       defaultCheck(peArray[i]);
	       	
	    }
   }
});



function onClick(id){
	var context_url = location.pathname;
	var root_url = context_url.substring(0,context_url.indexOf("index.php"));
    var divid="#ul_"+id.substr(4);
    id="#"+id;
    if($(divid).css("display")=="none"){
        $(id).attr("src",root_url+"Public/images/permission/minus.gif");
        $(divid).show();
    }
    else
    {
        $(id).attr("src",root_url+"Public/images/permission/plus.gif");
        $(divid).hide();
    }
}
function defaultCheck(id){
	if(id=="A"){
		$("#superManager").attr("checked","checked");
		$(".inputClass").attr("checked","checked");
	}
	else{
		var ulid="#ul_"+id;
    	$("#"+id).attr("checked","checked");
    	$(ulid).find("input").attr("checked","checked");
	}
}
function checkChange(id){
    var ulid="#ul_"+id;
    if($("#"+id).attr("class").substr(11)!="upLayer"){
        var parentId=$("#"+id).attr("class").substr(11);
        var parent_parentId=$("#"+parentId).attr("class").substr(11);
        if($("#"+id).attr("checked")){
            $(ulid).find("input").attr("checked","checked");
            var count=0;
            $("."+parentId).each(function(){
                if($(this).attr("checked")){
                    count++;
                }
            });
            if(count==$("."+parentId).length){
                $("#"+parentId).attr("checked","checked");
            }
            count=0;
            $("."+parent_parentId).each(function(){
                if($(this).attr("checked")){
                    count++;
                }
            });
            if(count==$("."+parent_parentId).length){
                $("#"+parent_parentId).attr("checked","checked");
             //   $("#superManager").attr("checked",true);   //当所有权限列表都选中时，超级管理员自动被选中。
            }
        }
        else{
            $("#"+parentId).attr("checked","");
            $("#"+parent_parentId).attr("checked","");
            $(ulid).find("input").attr("checked","");
            $("#superManager").attr("checked",false);   //添加互斥功能，当点击权限表不选中时，超级管理员会被不选中。
        }
  }
	else{
		/*
       if($("#"+id).attr("checked")){
          $(ulid).find("input").attr("checked","checked");
          var sum=$("input[type=checkbox]").length-1;//减去超级管理员复选框的其他复选框总数
          var checked_sum=$("input[type=checkbox]:checked").length;//已选中的复选框个数
        if(checked_sum==sum){
        	 $("#superManager").attr("checked",true);   //当所有权限列表都选中时，超级管理员自动被选中。
        	}
       }
       else{
       	
          $(ulid).find("input").attr("checked","");
          $("#superManager").attr("checked",false);   //添加互斥功能，当点击权限表不选中时，超级管理员会被不选中。 
       }
  } */
  		 if($("#"+id).attr("checked")){
          $(ulid).find("input").attr("checked","checked");
          var sum=$("input[type=checkbox]").length-1;//减去超级管理员复选框的其他复选框总数
          
         
	var input_tags = document.all.tags("INPUT");
	var checked_sum = 0;
	if (input_tags!=null)
	{
		for (i=0; i<input_tags.length; i++){ 
			if(input_tags[i].type=="checkbox" && input_tags[i].checked == true){
				checked_sum++;
			}
		}
	}
 
     if(checked_sum==sum){
          
        	// $("#superManager").attr("checked",true);   //当所有权限列表都选中时，超级管理员自动被选中。
 
        	}
       }else{
          $(ulid).find("input").attr("checked",""); 
          $("#superManager").attr("checked",false);   //添加互斥功能，当点击权限表时，超级管理员会被不选中。
       }
 	} 
}
function getPermission(){

    var perString="";
    if($("#superManager").attr("checked")){
    	perString="A";
    }
    else{
    	$(".upLayer").each(function(){
        var parentId1=$(this).attr("id");

        if($(this).attr("checked")){
            perString+=parentId1+" ";

         }
         else{
            $("."+parentId1).each(function(){
                var parentId2=$(this).attr("id");

                if($(this).attr("checked")){
                    perString+=parentId2+" ";

                }
                else{
                    $("."+parentId2).each(function(){
        							
                        if($(this).attr("checked")){
                            perString+=$(this).attr("id")+" ";

                        }
                    });
                }
            });
         }
      });
    }
     return perString;
}