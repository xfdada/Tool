<?php
/**
 * Created by PhpStorm.
 * User: wdsn
 * Date: 2019-01-09
 * Time: 16:39
 */
namespace lib\WxPay;
use think\Controller;
class WxPayCommon extends Controller
{
    private $key = "0a0af7ddf29466175c9150ce6c1efc6b";//商户key

    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj)
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


    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    public function ToXml($data)
    {

        if (!is_array($data) || count($data) <= 0) {
            echo("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($data as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
                //echo "xml1".$xml;
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
                //echo "xml2".$xml;

            }
        }
        $xml .= "</xml>";
        //echo "xml数据".$xml;
        return $xml;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml($xml)
    {
        if (!$xml) {
            die("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }

    /**
     * 设置签名，详见签名生成算法
     * @param string $value
     **/
    public function SetSign($params)
    {
        $sign = $this->MakeSign($params);
        return $sign;
    }

    /**
     * md5生成签名
     */
    public function MakeSign($params)
    {

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
    public function CheckSign($params)
    {
        $sign = $this->MakeSign($params);
        if ($params["sign"] == $sign) {
            //签名正确
            return true;
        }
        return false;
    }

    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param WxPayConfigInterface $config 配置对象
     * @param string $xml 需要post的xml数据
     * @param string $url url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second url执行超时时间，默认30s
     * @throws WxPayException
     */
    public static function postXmlCurl($config, $xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        $proxyHost = "0.0.0.0";
        $proxyPort = 0;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
        //curl_setopt($ch,CURLOPT_USERAGENT, $ua);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            //var_dump($data);
            return $data;
        } else {
            $error = curl_errno($ch);
            //var_dump($error);
            curl_close($ch);
            die ("curl出错，错误码:$error");
        }
    }


}