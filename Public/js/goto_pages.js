//跳转到第几页
function getPages(obj){
    var page= $.trim($('#goto_page').val());
    var totalpage=$("#totalPage").val();

    if(page==null||page==""){
        alert('跳转的页面编号不能为空');
        return false;
    }else if(!pageshuzi(page)){
        alert('跳转的页面编号必须为正整数');
        return false;
    }else if(page<=0){
        alert('跳转的页面编号必须大于0');
        return false;
    }
    if(parseInt(page)>parseInt(totalpage)){
        page=totalpage;
    }

    obj.href=obj.href+page;
}
//验证输入的是否为数据
function pageshuzi(str){
    var s = /^[0-9]*$/;
    return s.test(str);
}