<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/17
 * Time: 11:02
 */
header ( "Content-Type: text/html; charset=utf-8" );
require_once("config.php");
$config=CommonClass::getSiteConfig($clientA);
exit(APP_CONFIG_URL);