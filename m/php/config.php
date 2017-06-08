<?php
require_once("harris_sanitize.php");
$h_instance = new Harris_Sanitize();
$_POST = $h_instance->sanitize($_POST);
$rules = [
    'pcmn' => 'required|alpha_dash|max_len,12|min_len,10',
];
$filters = [
    'pcmn' => 'trim|sanitize_string',
];
$_POST = $h_instance->filter($_POST, $filters);
$validated = $h_instance->validate($_POST, $rules);
if ($validated !== true) {
    $declare_chinese = [
        'pcmn' => '牛牛',
    ];
    $v_error = $h_instance->get_readable_errors();
    $v_error = $h_instance->h_ack_message($v_error);
    $v_error = $h_instance->h_ack_translate($declare_chinese, $v_error);
    $re['errMsg'] = $v_error;
    echo  json_encode($re);
    exit();
} else {
    $pcmn_explode = explode('-',$_POST['pcmn']);
    $pcmn['pcmn1'] = $pcmn_explode[0];
    $pcmn['pcmn2'] = $pcmn_explode[1];
    $pcmn['pcmn3'] = $pcmn_explode[2];
    $pcmn = $h_instance->sanitize($pcmn);
    $rules = [
        'pcmn1' => 'required|numeric|max_len,4',
        'pcmn2' => 'required|numeric|max_len,3',
        'pcmn3' => 'required|numeric|max_len,3',
    ];
    $pcmn = $h_instance->filter($pcmn, $filters);
    $filters = [
        'pcmn1' => 'trim|intval',
        'pcmn2' => 'trim|intval',
        'pcmn3' => 'trim|intval',
    ];
    $validated = $h_instance->validate($pcmn, $rules);
    if ($validated !== true) {
        $declare_chinese = [
            'pcmn1' => '牛牛1',
            'pcmn2' => '牛牛2',
            'pcmn3' => '牛牛3',
        ];
        $v_error = $h_instance->get_readable_errors();
        $v_error = $h_instance->h_ack_message($v_error);
        $v_error = $h_instance->h_ack_translate($declare_chinese, $v_error);
        $re['errMsg'] = $v_error;
        echo  json_encode($re);
        exit();
    }
    else{
        define("SITE_ID", $pcmn['pcmn1']);
        define("IMG_SITE_ID", $pcmn['pcmn2']);
        define("SITE_TYPE", $pcmn['pcmn3']);
    }
}

    //define("SITE_ID", 99999);  //网站id
//define("SITE_ID", 8000);//define("SITE_ID", 6001);
//    define("SITE_TYPE", 1);  //副站
//    define("IMG_SITE_ID", 140);  //图片网站id
//    define("DS_SS_HANDICP", 'B');  //图片网站id
//    define("DS_XG_HANDICP", 'C');  //图片网站id

/*define("MY_HTTP_HOST", 'http://wcapisec.ds.com/api.php');//api地址
define("REDIS_HOST", '10.10.1.141');//redis地址*/
define("MY_HTTP_HOST", 'http://www.hwcapisec.com/api.php');//api地址
define("REDIS_HOST", 'localhost');//redis地址
define("REDIS_PORT", 6379);//redis端口
define("REDIS_DB", 7);//redis前端库
define("REDIS_PASS", '');//redis密码
if (function_exists('date_default_timezone_set')) {//设置美东时间时区
    @date_default_timezone_set('Etc/GMT+4');
}
require_once("src/Hprose.php");
require_once("CommonClass.php");
require_once("config_fromkeytype.php");

//$tr = 'dsafdsa'.$a."dsafdsa$t"."dsfadsa{$as['dfsa']}".'sdafds$a'."{$ty->aaaa}";


