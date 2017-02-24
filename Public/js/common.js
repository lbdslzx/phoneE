/**
 *  贵健康管理系统通用方法
 *  author          herry.yao <yuandeng.yao@longmatser.com.cn>
 *  datetime        2016-06-14 09:24:44
 *  version         v1.0
 */
;!function(){
    //加载扩展模块
    layer.config({
        extend: 'extend/layer.ext.js'
    });
}();
function bombBox(url,title,bWidth,bHeight,bScroll,type) {
    layer.open({
        type: type,
        title: title,
        fix: false,
        shadeClose: true,
        maxmin: false,
        area: [bWidth, bHeight],
        content: [url,bScroll],
        end: function(){}
    });
}
function bombBoxDiv(div,title,bWidth,bHeight) {
    var index = layer.open({
        type: 1,
        title: title,
        fix: false,
        shadeClose: true,
        maxmin: false,
        area: [bWidth, bHeight],
        content: div,
        end: function(){}
    });
    return index;
}