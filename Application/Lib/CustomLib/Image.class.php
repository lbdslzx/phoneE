<?php
/**
 * @example      图片处理类
 * @file         Image.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2016-06-16
 * @time         14:00
 * @copyright © 2016, Longmaster Corporation. All right reserved.
 */
class Image{
    /**
     * 获取图片信息
     * @param $imgPath
     * @return array|bool
     */
    public static function getImageInfo($imgPath) {
        $imageInfo = getimagesize($imgPath);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($imgPath);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
            return $info;
        } else {
            return false;
        }
    }
    /**
     * 生成缩略图
     * @static
     * @access public
     * @param string $image  原图
     * @param string $thumbPath 缩略图文件名
     * @param string $maxWidth  宽度
     * @param string $maxHeight  高度
     * @param string $position 缩略图保存目录
     * @param boolean $interlace 启用隔行扫描
     * @return void
     */
    public static function thumb($image, $thumbPath, $maxWidth=200, $maxHeight=50, $interlace=true,$type=null) {
        // 获取原图信息
        $info = self::getImageInfo($image);
        if(is_array($info)){
            $srcWidth = $info['width'];
            $srcHeight = $info['height'];
            $type = $type ? $type : $info['type'];
            $type = strtolower($type);
            $interlace = $interlace ? 1 : 0;
            $scale = min($maxWidth / $srcWidth, $maxHeight / $srcHeight); // 计算缩放比例
            if($scale >= 1){
                $width = $srcWidth;
                $height = $srcHeight;
            }else{
                $width = intval($srcWidth * $scale);
                $height = intval($srcHeight * $scale);
            }
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            if(!function_exists($createFun)) {
                return false;
            }
            $srcImg = $createFun($image);
            //创建缩略图
            if ($type != 'gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($width, $height);
            else
                $thumbImg = imagecreate($width, $height);
            //png和gif的透明处理 by luofei614
            if('png'==$type){
                imagealphablending($thumbImg, false);//取消默认的混色模式（为解决阴影为绿色的问题）
                imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息（为解决阴影为绿色的问题）    
            }elseif('gif'==$type){
                $trnprt_indx = imagecolortransparent($srcImg);
                if ($trnprt_indx >= 0) {
                    //its transparent
                    $trnprt_color = imagecolorsforindex($srcImg , $trnprt_indx);
                    $trnprt_indx = imagecolorallocate($thumbImg, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                    imagefill($thumbImg, 0, 0, $trnprt_indx);
                    imagecolortransparent($thumbImg, $trnprt_indx);
                }
            }
            // 复制图片
            if (function_exists("ImageCopyResampled"))
                imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            else
                imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);

            // 对jpeg图形设置隔行扫描
            if ('jpg' == $type || 'jpeg' == $type)
                imageinterlace($thumbImg, $interlace);

            // 生成图片
            $imageFun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
            $imageFun($thumbImg, $thumbPath);
            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            return $thumbPath;
        }
        return false;
    }
}