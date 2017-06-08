<?php
header("content-Type: text/html; charset=utf-8");
require_once("CommonClass.php");
echo CommonClass::getLoadingPage($_GET['logo'],'#ffffff');