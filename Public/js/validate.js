/**
 * 表单验证方法文件
 **/

//用途：检查输入字符串是否符合正整数格式
function isNumber(str){ 
    var expression = "^[0-9]+$"; 
    var regular = new RegExp(expression); 
    if (str.search(regular) != -1) { 
        return true; 
    }
     else { 
        return false; 
    } 
} 

//用途：检查输入字符串是否为空或者全部都是空格 
function isNull(str){ 
    if ( str == "" ) 
        return true; 
    var expression = "^[ ]+$"; 
    var regular = new RegExp(expression); 
        return regular.test(str); 
}


//用途：检查输入对象的值是否符合E-Mail格式 
function isEmail(str){ 
    var myReg = /^[-_A-Za-z0-9]+@([_A-Za-z0-9]+\.)+[A-Za-z0-9]{2,3}$/; 
    if(myReg.test(str)){ 
        return true; 
    }
    return false; 
} 

//用途：检查输入字符串是否只由英文字母和数字和下划线组成 
function isNumberOr_Letter(str){
    var expression = "^[0-9a-zA-Z\_]+$"; 
    var regular = new RegExp(expression); 
    if (regular.test(str)) { 
        return true; 
    }
    else{ 
        return false; 
    } 
} 


//用途：检查输入字符串是否只由汉字、字母、数字组成 
function isChineseOrNumbOrLett(str){
    var expression = "^[0-9a-zA-Z\u4e00-\u9fa5]+$"; 
    var regular = new RegExp(expression); 
    if (regular.test(str)) { 
        return true; 
    }
    else{ 
        return false; 
    } 
} 

 
//用途：判断是否是日期 
function isDate( date, fmt ) { 
    if (fmt==null) 
        fmt="yyyyMMdd"; 
    var yIndex = fmt.indexOf("yyyy"); 
    if(yIndex==-1) 
        return false; 
    var year = date.substring(yIndex,yIndex+4); 
    var mIndex = fmt.indexOf("MM"); 
    if(mIndex==-1) 
        return false; 
    var month = date.substring(mIndex,mIndex+2); 
    var dIndex = fmt.indexOf("dd"); 
    if(dIndex==-1) 
        return false; 
    var day = date.substring(dIndex,dIndex+2); 
    if(!isNumber(year)||year>"2100" || year< "1900") 
        return false; 
    if(!isNumber(month)||month>"12" || month< "01") 
        return false; 
    if(day>getMaxDay(year,month) || day< "01") 
        return false; 
    return true; 
} 
//获取月份天数isDate()中调用
function getMaxDay(year,month) { 
    if(month==4||month==6||month==9||month==11) 
        return "30"; 
    if(month==2) 
    if(year%4==0&&year%100!=0 || year%400==0) 
        return "29"; 
    else 
        return "28"; 
    return "31"; 
} 

//用途：检测两次输入密码是否一致
function isSame(str1,str2)  {  
    if (str1==str2)  {
        return(true);
    }  
    else{
        return(false);
    }  
}  


//用途：检查输入的固定电话号码格式是否正确
function checkPhone( strPhone ) { 
    var phoneRegWithArea = /^[0][1-9]{2,3}-[0-9]{5,10}$/; 
    var phoneRegNoArea = /^[1-9]{1}[0-9]{5,8}$/; 
    if( strPhone.length > 9 ) {
        if( phoneRegWithArea.test(strPhone) ){
            return true; 
        }
        else{
            return false; 
        }
    }
    else{
        if( phoneRegNoArea.test( strPhone ) ){
            return true; 
        }
        else{
            return false; 
        }
    }
}
//用途：检测输入的邮政编号是否正确
function isZip(str){
    var expression = /^[1-9]\d{5}$/; 
    var regular = new RegExp(expression); 
    if (regular.test(str)) { 
        return true; 
    }
    else{ 
        return false; 
    } 
}
//用途：验证开始时间是否小于结束时间
function check_time(begin, end, msg) {
    var begin_time = $(begin).val();
    var end_time = $(end).val();
    if (begin_time > end_time) {
        $(msg).html('开始时间不能大于结束时间！');
        return false;
    }else{
        return true;
    }
} 







 
