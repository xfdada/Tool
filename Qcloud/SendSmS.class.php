<?php

require "SmsSenderUtil.php";
require "SmsSingleSender.php";
require_once "SmsMultiSender.php";
require_once "SmsStatusPuller.php";
require_once "SmsMobileStatusPuller.php";

use Qcloud\SmsSingleSender;

class SendSmS
{
    // 短信应用SDK AppID
    private $appid = 1400*****; // 1400开头

    // 短信应用SDK AppKey
    private $appkey = "***********";

    /**单发
     * @param $phoneNumbers     要发送验证码的手机号码
     * @param $params    array[522522,5] 有两个参数 1是验证码，2是有效时间
     * @return string|void
     */

    public function Send($phoneNumbers, $params)
    {
//        $phoneNumbers = "13026209544";
// 短信模板ID，需要在短信应用中申请
        $templateId = *****;  // NOTE: 这里的模板ID`7839`只是一个示例，真实的模板ID需要在短信控制台中申请
// 签名
        $smsSign = ""; // NOTE: 这里的签名只是示例，请使用真实的已申请的签名，签名参数使用的是`签名内容`，而不是`签名ID`
// 指定模板ID单发短信
        try {
            $ssender = new SmsSingleSender($this->appid, $this->appkey);
            $result = $ssender->sendWithParam("86", $phoneNumbers, $templateId,
                $params, $smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
            $rsp = json_decode($result);
            return $result;
        } catch (\Exception $e) {
            return var_dump($e);
        }

    }


}
