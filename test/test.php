<?php
include __DIR__ . '/../src/Curl.php';
include __DIR__ . '/../src/Result.php';

use sreeb\Curl;

$curl = Curl::getInstance();
$result = $curl
    ->setHeader(['header0' => '0', 'header1' => '1'])//array设置header
    ->setHeader('header2', '2')//键值设置header
    ->setCookie(['cookie0' => '0', 'cookie1' => '1'])//array设置cookie
    ->setCookie('cookie2', '2')//键值设置cookie
    ->setCookie('./cookie.txt')//文件设置cookie
    ->setResponseCookieStorable('./cookie.txt')//设置响应cookie存储路径
    ->setTimeout(3, 3)//链接超时,等待超时
    ->setSSL(false)//跳过SSL证书验证
    ->setProxy('192.168.0.1', '8080', 'username', '123456')//设置代理IP访问
    ->request('http://www.example.com/', 'post', ['parameter' => 'value', 'file' => new \CURLFile(realpath('./image.png'), 'image/png', 'image')], false)
    ->setLocation(true, 1)//获取重定向后的内容，最多重定向次数
    ->send(false);//发送请求（是否关闭cUrl资源，默认关闭,不关闭可以继续使用$curl进行请求，减少资源开销和重复设置项），返回响应结果对象

if ($result->getErrno() == 0) {
    print_r($result->getResponse());//获取响应数据(含header头)
    print_r($result->getInfo());//获取curlinfo
    print_r($result->getInfo('http_code'));//获取HTTP响应码
    print_r($result->getHeader());//获取header
    print_r($result->getBody());//获取内容
} else {
    print_r($result->getError());
}











