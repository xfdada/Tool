# QRcode

. 该类是生成居中带logo的二维码

. 引入该类实例化

$qr = new QRcodes();
$img = $qr->create_Code('二维码中的值','logo图片的地址');
echo "<img src='{$img}.png'/>";