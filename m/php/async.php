<?php
/*SSE*/
//header('Content-Type: text/event-stream');
//header('Cache-Control: no-cache');

//ini_set("display_errors", "on");
require_once("config.php");
$re='';
switch ($action) {
    case 'navtab':
        $OG_Tab = '2'; //1.OG开启2.OG关闭
        
        /*1不展示,2.不必填,3.必填*/
        $email_Register = '3'; //邮箱必填
        $email_Register = '1'; //邮箱不展示
        $mobile_Register='2';//号码不必填
        $realname_Register='2';
        /*代理*/
        $agent_realname_Register = '2';
        $agent_mobile_Register = '2';
        $agent_email_Register = '3';
        $agent_qq_Register = '3';
        $ON_OFF=array(
            'OG_Tab'=>$OG_Tab
        );
        $ON_OFF_register=array(
            'email_Register'=>$email_Register,
            'mobile_Register'=>$mobile_Register
            
        );
        $ON_OFF_agent_register=array(
            'agent_realname_Register'=>$agent_realname_Register,
            
        );
        $re=array('code'=>'success','ONOFF'=>$ON_OFF,'ON_OFF_register'=>$ON_OFF_register,'ON_OFF_agent_register'=>$ON_OFF_agent_register);
        break;
}
echo CommonClass::ajax_return($re, $jsonp, $jsonpcallback);

/*SSE推送*/
//$re=CommonClass::ajax_return($re, $jsonp, $jsonpcallback);
//if (3 == 1) {
//    echo 'id:123'.PHP_EOL;
//    echo 'data:' . $re . PHP_EOL ;
//    echo PHP_EOL;
//    ob_flush();
//    flush();
//}
