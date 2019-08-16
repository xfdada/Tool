<?php
/**
 * Created by PhpStorm.
 * User: wdsn
 * Date: 2019-01-09
 * Time: 16:39
 */

namespace lib\RefundPay;

use think\Controller;

class WxPayCommon extends Controller {

    private $key = "0a0af7ddf29466175c9150ce6c1efc6b"; #商户key

    /**
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public static function getNonceStr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }


    /**
     * 将array转为xml字符串
     * @param array $data
     **/
    public function ToXml($data) {
        if (!is_array($data) || count($data) <= 0) {
            echo("数组数据异常！");
        }
        $xml = "<xml>";
        foreach ($data as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }


    /**
     * 将xml转为array
     * @param string $xml
     */
    public function FromXml($xml) {
        if (!$xml) {
            die("xml数据异常！");
        }
        //将XML转为array 禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }


    /**
     * 拼接签名字符串
     * @param array $urlObj
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj) {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            if ($k != "sign") {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }


    /**
     * 设置签名，详见签名生成算法
     * @param string $value
     **/
    public function SetSign($params) {
        //签名步骤一：按字典序排序参数
        ksort($params);
        $string = $this->ToUrlParams($params);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $this->key;
        //echo "string".$string;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }


    /**
     * @param
     * 检测签名
     */
    public function CheckSign($params) {
        $sign = $this->SetSign($params);
        if ($params["sign"] == $sign) {
            //签名正确
            return true;
        }
        return false;
    }


    /**
     * 以post方式提交xml到对应的接口url
     * @param string $url 请求地址
     * @param string $xml 携带XML数据
     * @param int $timeout 执行超时时间
     * @return 成功返回data 失败返回curl_errno
     */
    public static function postXmlCurl($url, $xml, $timeout=30) {
        $ch = curl_init();

        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验

        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        //执行
        $data = curl_exec($ch);
        $errno = curl_errno($ch);

        //关闭流
        curl_close($ch);

        //返回结果
        if ($data) return $data;
        else       die("curl出错，错误码:$errno");

    } //curl-post 无证书



    /**
     * 以post方式提交xml到对应的接口url,并使用证书
     * @param string $url 请求地址
     * @param string $xml 携带XML数据
     * @param int $timeout 执行超时时间
     * @return 成功返回data 失败返回curl_errno
     */
    public static function postXmlCurlWithCert($url, $xml,  $timeout=30) {
        $ch = curl_init();

        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        //设置header 和 post提交方式
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); #输出字符流
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        #定义商户证书
        $sslCertPath = "/etc/apache2/ssl/wxpay/apiclient_cert.pem";
        $sslKeyPath = "/etc/apache2/ssl/wxpay/apiclient_key.pem";

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);    #严格校验
        curl_setopt($ch, CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, $sslCertPath);
        curl_setopt($ch, CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, $sslKeyPath);

        //运行curl
        $data = curl_exec($ch);
        $errno = curl_errno($ch);

        //关闭流
        curl_close($ch);

        //返回结果
        if ($data) return $data;
        else       die("curl出错，错误码:$errno");
        
    } //curl-post 有证书
} //class

