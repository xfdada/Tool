<?php

include_once 'phpqrcode.php';


class QRcodes{

    /**
     * 生成居中logo的二维码
     * @param $value  string  二维码中的内容值
     * @param $logo string 要加入的 logo图片地址
     * @return false|string
     */
    public function create_Code($value,$logo){

        $errorCorrectionLevel = 'H';//容错级别
        $matrixPointSize = 5;//生成图片大小
        QRcode::png($value, '1223.png', $errorCorrectionLevel, $matrixPointSize, 2);//生成的二维码图片

        if ($logo !== FALSE) {
        $QR = imagecreatefromstring(file_get_contents('1223.png'));
        $logo = imagecreatefromstring(file_get_contents($logo));
        $QR_width = imagesx($QR);//二维码图片宽度
        $QR_height = imagesy($QR);//二维码图片高度
        $logo_width = imagesx($logo);//logo图片宽度
        $logo_height = imagesy($logo);//logo图片高度
        $logo_qr_width = $QR_width / 5;
        $scale = $logo_width/$logo_qr_width;
        $logo_qr_height = $logo_height/$scale;
        $from_width = ($QR_width - $logo_qr_width) /2;
        $from_height =$QR_height- ($QR_height-$logo_height)/3;
        //重新组合图片并调整大小
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
            $logo_qr_height, $logo_width, $logo_height);
    }
    $path = date('YmdHis');
    imagepng($QR, $path.".png");

   return $path;
    }
}