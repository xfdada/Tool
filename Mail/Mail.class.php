<?php
require_once "Smtp.class.php";
//******************** 配置信息 ********************************

class Mail{

    //************************ 配置信息 ****************************
    private $smtpserver = "smtp.163.com";//SMTP服务器 或者 smtp.qq.com
    private $smtpserverport =25;//SMTP服务器端口
    private $smtpusermail = "xiangfudada@163.com";//SMTP服务器的用户邮箱
    private $smtpuser = "xiangfudada@163.com";//SMTP服务器的用户帐号，注：部分邮箱只需@前面的用户名
    private $smtppass = "**********";//SMTP服务器的授权码  请到邮箱设置里面进行申请

    /**
     * @param $smtpemailto   要发送给谁  发送者的邮箱地址
     * @param $mailtitle     邮件的标题
     * @param $mailcontent   邮件的内容
     * @return mixed        返回array  msg提示信息 code 状态码 1成 5 失败
     */
    public function Send($smtpemailto,$mailtitle,$mailcontent){

        $mailtype = "HTML";//邮件格式（HTML/TXT）,TXT为文本邮件
        $smtp = new Smtp($this->smtpserver,$this->smtpserverport,true,$this->smtpuser,$this->smtppass);//这里面的一个true是表示使用身份验证,否则不使用身份验证.
        $smtp->debug = false;//是否显示发送的调试信息
        $state = $smtp->sendmail($smtpemailto, $this->smtpusermail, $mailtitle, $mailcontent, $mailtype);
        if($state==""){
            $data['msg'] = "对不起，邮件发送失败！请检查邮箱填写是否有误。";
            $data['code'] = 5;
            return $data;
        }
        $data['msg'] = "恭喜！邮件发送成功！！";
        $data['code'] = 1;
        return $data;

    }
}


?>
