<?php
/**
 * @example
 * @file         Common.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2016/1/22 0022
 * @time         17:12
 */
class Common{
    /**
     * AES加密
     * @param $str 待加密字符串
     * @param $key
     * @return string
     * @throws \Exception
     */
    static function aes_encrypt($str,$key = null){
        $aes_key = $key ? $key : C("push_cfg.aes_key");
        $aes = new CryptAES();
        $aes->set_key($aes_key);
        $aes->require_pkcs5();
        return $aes->encrypt($str);
    }

    /**
     * AES解密
     * @param $str 待解密字符串
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    static function aes_decrypt($str,$key = null){
        $aes_key = $key ? $key : C("push_cfg.aes_key");
        $aes = new CryptAES();
        $aes->set_key($aes_key);
        $aes->require_pkcs5();
        return $aes->decrypt($str);
    }
    /**
     * @param string $server 服务器地址
     * @param string $curlPost post数据，如：name=toy&age=22
     * @return string 页面返回值
     */
    public static function post($server,$curlPost){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $server);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 80);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}