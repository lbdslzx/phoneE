<?php
/**
 *
 * Excel操作 方法。
 */

Vendor('PHPExcel',THINK_PATH.'Extend/Vendor/Excel','.php');

class Excel{


    /**
     * Excel导出
     * $data //表格数组, $tableheader //表头数组, $letter
     * $data = array(
        '0' => array('1','xiaowang','ios'),
        '1' => array('2', 'xiaozhang', 'android')
        )
       $tableheader = array('订单ID','就诊医院','就诊科室','就诊医生','门诊类型','就诊时间','就诊人','性别','年龄','身份证','就诊卡类型','卡号','地址')
       $filename //文件名称
     */
    function export($tableheader,$data,$filename){
        $excel =  new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        //填充表头信息
        for($i = 0;$i < count($tableheader);$i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
        }
        //填充表格信息
        for ($i = 2;$i <= count($data) + 1;$i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key=>$value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i",' '.$value);
                $j++;
            }
        }
        $write = new PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="'.$filename.'.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }
}