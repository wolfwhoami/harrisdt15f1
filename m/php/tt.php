<?php
//$a='{
//    "appid":"H559B6C32",
//    "iOS":{
//    	"version":"1.1.3",
//    	"title":"兰博更新",
//    	"note":"优化界面操作用户体验效果；\n修复一些其它已知的bug。\n",
//    	"url":"http://wcphp.ds.com/download/113.wgt"
//    },
//    "Android":{
//    	"version":"2.0.6",
//    	"title":"兰博更新",
//    	"note":"优化界面操作用户体验效果；\n修复一些其它已知的bug。\n",
//    	"url":"http://wcphp.ds.com/download/206.wgt"
//    }}';
//echo $a; 
header ( "Content-Type: text/html; charset=utf-8" );
require_once("config.php");
$config=CommonClass::getSiteConfig($clientA);
echo htmlspecialchars_decode (mixup);
