/**
 *  贵健康管理系统图片验证通用方法
 *  author          herry.yao <yuandeng.yao@longmatser.com.cn>
 *  datetime        2016-06-14 09:24:44
 *  version         v1.0
 */
function CheckImg(f,p){
    //判断图片尺寸
    var img=null;
    img=document.createElement("img");
    document.body.insertAdjacentElement("beforeend",img);
    img.style.visibility="hidden";
    img.src=f;
    var imgWidth=img.offsetWidth;
    var imgHeight=img.offsetHeight;
    if(p.name=="UpFile_Photo1"){
        
    }
}