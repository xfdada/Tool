<?php
namespace lib\RefundPay;

use think\App;
use think\Model;
use think\Controller;
use think\Cache;
use think\Config;
use think\Db;
use think\Log;

class WxPay extends WxPayCommon{
    private $appid = "wx41e8fb1a21385bc6"; #小程序ID
    private $mch_id = "1380014902";        #商户ID
    private $device_info = "1000";         #设备号
    private $key = "0a0af7ddf29466175c9150ce6c1efc6b"; #商户key

    public function __construct($privatePage = false){
        parent::__construct();
    }

    /**
     * 调用微信退款API
     * @author xian
     * @param String out_trade_no  	订单号
     * @param String out_refund_no	退款订单号，系统自己生成,【44开头】
     * @param Int total_fee        	订单总额
     * @param Int refund_fee      	退款金额 ,一般都是一次性退完，不会分批次退除非公司没钱
     * @return Bool 			 	执行是否成功
     */
    public function Refund($refund){
        //退款地址
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";

        //异步回调
        #$notify_url = "http://www.bxrcwls.cn/api/Wxpay/RefundNotify";
        
        $inputObj["appid"] = $this->appid;//公众账号ID
        $inputObj["mch_id"] = $this->mch_id;//商户号
        $inputObj["out_trade_no"] = $refund['out_trade_no'];//商户订单号
        $inputObj["out_refund_no"] = $refund['out_refund_no'];//商户退款单号
        $inputObj["total_fee"] = $refund['total_fee'];//订单金额
        $inputObj["refund_fee"] = $refund['refund_fee'];//退款金额
        $inputObj["nonce_str"] = self::getNonceStr();  //随机字符串;
        $inputObj["sign"] = $this->SetSign($inputObj); //进行签名和保存;

        //将数据转成微信XML格式
        $xml = $this->ToXml($inputObj);

        //请求xml写入日志
        @file_put_contents("weixin.log", $xml . "/n", FILE_APPEND);

        //调用带证书的POST请求
        $response = self::postXmlCurlWithCert($url, $xml, 60);
        dump($response);exit;
        //返回xml写入日志
        @file_put_contents("weixinp.log", $response . "/n", FILE_APPEND);

        //将微信传回的XML转成数组
        $data = $this->FromXml($response);

#<xml><return_code><![CDATA[SUCCESS]]></return_code>
#<return_msg><![CDATA[OK]]></return_msg>
#<appid><![CDATA[wx41e8fb1a21385bc6]]></appid>
#<mch_id><![CDATA[1380014902]]></mch_id>
#<nonce_str><![CDATA[oQDay40MqTS39Fis]]></nonce_str>
#<sign><![CDATA[45229ECDC1C313621A97BF00B2747F1E]]></sign>
#<result_code><![CDATA[SUCCESS]]></result_code>
#<transaction_id><![CDATA[4200000250201903138702194512]]></transaction_id>
#<out_trade_no><![CDATA[992019031318103600007149]]></out_trade_no>
#<out_refund_no><![CDATA[4e38d5539b79f03170eab8219874b1ec]]></out_refund_no>
#<refund_id><![CDATA[50000010192019031308712075529]]></refund_id>
#<refund_channel><![CDATA[]]></refund_channel>
#<refund_fee>100</refund_fee>
#<coupon_refund_fee>0</coupon_refund_fee>
#<total_fee>100</total_fee>
#<cash_fee>100</cash_fee>
#<coupon_refund_count>0</coupon_refund_count>
#<cash_refund_fee>100</cash_refund_fee>
#</xml>

        if($data['return_code'] == "SUCCESS"){
            if(isset($data['err_code']) && $data['err_code'] == "ERROR"){
                #dump($data);
                return ['res'=>false, 'msg'=>$data['err_code_des']];
            }else{
                return ['res'=>true, 'msg'=>''];
            }
        }

        return ['res'=>false, 'msg'=>$data['err_code_des']];
    } //Refund



    public function RefundHttp(){
        //退款地址
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";

        $inputObj["appid"] = $this->appid;
        $inputObj["mch_id"] = $this->mch_id;
        $inputObj["out_trade_no"] = input("no");
        $inputObj["out_refund_no"] = md5(time());
        $inputObj["total_fee"] = input("fee");
        $inputObj["refund_fee"] = input("fee");
        $inputObj["nonce_str"] = self::getNonceStr();  //随机字符串;
        $inputObj["sign"] = $this->SetSign($inputObj); //进行签名和保存;

        
        #dump($inputObj);exit;

        //将数据转成微信XML格式
        $xml = $this->ToXml($inputObj);

        //请求xml写入日志
        @file_put_contents("weixin.log", $xml . "/n", FILE_APPEND);

        //调用带证书的POST请求
        $response = self::postXmlCurlWithCert($url, $xml, 60);

        //返回xml写入日志
        @file_put_contents("weixinp.log", $response . "/n", FILE_APPEND);

        //将微信传回的XML转成数组
        $data = $this->FromXml($response);

        dump($data);exit;

        if($data['return_code'] == "SUCCESS") return true;
        else return false;

    } //RefundHttp
}//class
?>
