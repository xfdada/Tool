<?php
/**
 * 异步
 */

$url = 'http://'.$_SERVER['HTTP_HOST'].'/img.php';//模拟请求的地址
doRequest($url);//

function doRequest($url, $param=array()){
    $urlinfo = parse_url($url);
    $host = $urlinfo['host'];
    $path = $urlinfo['path'];
    $query = isset($param)? http_build_query($param) : '';//post请求参数
    $port = 80;//请求的端口号
    $errno = 0;
    $errstr = '';
    $timeout = 10;//超时时间
    $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
    $out = "POST ".$path." HTTP/1.1\r\n";//请求类型为post 
    $out .= "host:".$host."\r\n";
    $out .= "content-length:".strlen($query)."\r\n";
    $out .= "content-type:application/x-www-form-urlencoded\r\n";
    $out .= "connection:close\r\n\r\n";
    $out .= $query;
    fputs($fp, $out);
    fclose($fp);
}
//注意：当执行过程中，客户端连接断开或连接超时，都会有可能造成执行不完整，因此需要加上
ignore_user_abort(true); // 忽略客户端断开
set_time_limit(0);    // 设置执行不超时
