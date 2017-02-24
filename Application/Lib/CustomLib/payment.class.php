<?php
/**
 * @example      支付相关处理
 * @file         payment.class.php
 * @author       Herry.Yao <yuandeng.yao@longmaster.com.cn>
 * @version      v1.0
 * @date         2016-03-25
 * @time         14:21
 * @copyright © 2016, Longmaster Corporation. All right reserved.
 */
class payment{
    /**
     * 请求退款
     * @param $orderId
     * @param $tradeId
     * @param $refundId
     * @param $payType
     * @return bool
     */
    public static function requestForRefund($orderId,$tradeId,$refundId,$payType,$reason){
        switch($payType){
            case 1:
                $payment = "ALIPAY";
                break;
            case 2:
                $payment = "WXPAY";
                break;
            case 3:
                $payment = "UNIONPAY";
                break;
            default:
                return false;
        }
        $data = [
            'order_id' => $orderId,
            'trade_id' => $tradeId,
            'refund_id'=> $refundId,
            'pay_type' => $payType,
            'reason'   => $reason
        ];
        return self::_refundPost($payment,$data);
    }
    private static function _refundPost($payment,$refund){
        $xml = ArrayToXML::createXML('refund',$refund);
        $xml = $xml->saveXML();
        $xml = strtr($xml,["\t"=>"","\n"=>"","\r"=>""]);
        $url = C("currency_cfg.currency_url");
        $key = C("currency_cfg.currency_aes_key");
        $refund = Common::aes_encrypt($xml,$key);
        $request = [
            'op_type'   => 7009,
            'payment'   => $payment,
            'refund'    => $refund
        ];
        $request = json_encode($request);
        $result = Common::post($url,['json'=>$request]);
        $result = json_decode($result,true);
        if(isset($result['code']) && $result['code'] == 0 && isset($result['refund'])){
            $result['refund'] = Common::aes_decrypt($result['refund'],$key);
        }
        return $result;
        //return isset($result['code']) && $result['code'] == 0 ? true : false;
    }
}