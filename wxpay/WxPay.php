<?php
/**
 * Created by PhpStorm.
 * User: wdsn
 * Date: 2019-01-09
 * Time: 15:38
 */

namespace lib\WxPay;
use think\Log;
use lib\WxPay\WxPayCommon;

class WxPay extends WxPayCommon
{
    private $appid = "wx41e8fb1a21385bc6";//小程序ID
    private $mch_id = "1380014902";//商户id
    private $device_info = "1000";//设备号
    private $key = "0a0af7ddf29466175c9150ce6c1efc6b";//商户key
    public function __construct($privatePage = false)
    {
        parent::__construct();
//        $config = Config::get('database');
//        $this->connection = Db::connect($config['AccessData']);

    }
    public function UnifiedOrder()
    {
        //统一下单地址
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        //异步回调
        $notify_url = "http://www.yuan.com/api/WxPay/Notify";
        $openid = input("openid", "opc6W5O117ezOnjsPFuNkSU6wQJ0");//openid
        $out_trade_no = input("out_trade_no", "992019031517462900025853");//订单编号
        $total_fee = input("total_fee", 0.01);
        //构造请求数据
        $inputObj["appid"] = $this->appid;  //appid
        $inputObj["openid"] = $openid;      //用户的openid
        $inputObj["mch_id"] = $this->mch_id;//商户id
        $inputObj["device_info"] = $this->device_info;//设备号
        $inputObj["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'];//当前IP地址
        $inputObj["out_trade_no"] = $out_trade_no;//订单号 后台生成
        $inputObj["body"] = "test";     //商品名称
        $inputObj["total_fee"] = 1;     //费用 单位是分
        $inputObj["trade_type"] = "JSAPI";// 支付模式
        $inputObj["notify_url"] = $notify_url;//回调地址
        $inputObj["nonce_str"] = self::getNonceStr();//随机字符串
        $inputObj["sign"] = $this->SetSign($inputObj);//签名

        $xml = $this->ToXml($inputObj);

        @file_put_contents("weixin.log", $xml . "/n", FILE_APPEND);

        $response = self::postXmlCurl("", $xml, $url, false, 60);
        //$response =
        //"<xml><return_code><![CDATA[SUCCESS]]></return_code>
        //<return_msg><![CDATA[OK]]></return_msg>
        //<appid><![CDATA[wx41e8fb1a21385bc6]]></appid>
        //<mch_id><![CDATA[1380014902]]></mch_id>
        //<device_info><![CDATA[1000]]></device_info>
        //<nonce_str><![CDATA[LHun7tO2MikWgVSM]]></nonce_str>
        //<sign><![CDATA[61909B26E1561C89901727891E4A63B0]]></sign>
        //<result_code><![CDATA[SUCCESS]]></result_code>
        //<prepay_id><![CDATA[wx151342328133507d6c457e421269633600]]></prepay_id>
        //<trade_type><![CDATA[JSAPI]]></trade_type>
        //</xml>
        @file_put_contents("weixinp.log", $response . "/n", FILE_APPEND);

        $data = $this->FromXml($response);

        $returnData["appId"] = $this->appid;
        $returnData["timeStamp"] = time();
        $returnData["nonceStr"] = self::getNonceStr();
        $returnData["signType"] = "MD5";
        $returnData["prepayId"] = "MD5";
        $returnData["package"] = "Sign=WXPay";
        if(!isset($data['prepay_id']) || isset($data['err_code_des'])){
            $returnData["error_msg"] = $data['err_code_des'];
        }else{
            $returnData["prepayId"] =$data["prepay_id"];
        }
        $returnData["paySign"] = $this->SetSign($returnData);
        //      $data = array(6) {
        //        ["appId"] => string(18) "wx41e8fb1a21385bc6"
        //        ["timeStamp"] => int(1565848094)
        //        ["nonceStr"] => string(32) "5lm728v7h6cr6pbfdlabq1gjy03vi1o6"
        //        ["signType"] => string(3) "MD5"
        //        ["package"] => string(46) "Sign=WXPay"
        //        ["prepayId"] => string(36) "wx151348168819687d6c457e421173127500"
        //        ["paySign"] => string(32) "3FE86CC46216B5D87F02441BAB2783E3"
        //         }
        return $this->result($returnData, 1, '', 'json');
    }


    /**
     * 入账回调
     */
    public function Notify()
    {
        //$xml = '<xml><appid><![CDATA[wx41e8fb1a21385bc6]]></appid><bank_type><![CDATA[CFT]]></bank_type><cash_fee><![CDATA[1]]></cash_fee><device_info><![CDATA[1000]]></device_info><fee_type><![CDATA[CNY]]></fee_type><is_subscribe><![CDATA[N]]></is_subscribe><mch_id><![CDATA[1380014902]]></mch_id><nonce_str><![CDATA[wbnolc4mi2xzh22kacup5cjviq1vcqop]]></nonce_str><openid><![CDATA[opc6W5O117ezOnjsPFuNkSU6wQJ0]]></openid><out_trade_no><![CDATA[1364b9b943ff22b608047d614ffd6350]]></out_trade_no><result_code><![CDATA[SUCCESS]]></result_code><return_code><![CDATA[SUCCESS]]></return_code><sign><![CDATA[9C34F85709479228DF7F3AE854EACAB9]]></sign><time_end><![CDATA[20190111154959]]></time_end><total_fee>1</total_fee><trade_type><![CDATA[JSAPI]]></trade_type><transaction_id><![CDATA[4200000248201901117260914997]]></transaction_id></xml>';
        //echo "xml".$xml;
        //XML格式转换

        $notifiedData = file_get_contents('php://input');
        //$notifiedData = $xml;
        //@file_put_contents("Notify.log", $notifiedData . "<br/>", FILE_APPEND);
        //echo htmlspecialchars($notifiedData);
        @file_put_contents("Notify.log", "xml" . $notifiedData . "\n", FILE_APPEND);
        $data = $this->FromXml($notifiedData);
        //var_dump($data);

        if ($data['return_code'] == "SUCCESS" && $data['result_code'] == "SUCCESS") {

            $re = $this->CheckSign($data);
            if (!$re) {
                echo "签名失败";
            } else {
                echo "成功";
                //处理入账功能
                $status = $this->ToMoney($data);
                if ($status == true) {
                    //入账成功，给微信反馈消息
                    //阻止微信接口反复回调接口  文档地址 https://pay.weixin.qq.com/wiki/doc/api/H5.php?chapter=9_7&index=7，下面这句非常重要!!!
                    $str = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                    //echo $str;

                } else {
                    //入账失败
                }
            }
        } else {
            die("通信失败");
        }
    }

    public function ToMoney($data) {


    }

    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private
    function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            if ($k != "sign") {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }
}
