<?php
     /**
     * @SWG\Post(
     *   path="/ali.php",
     *   tags={"存提款"},
     *   summary="支付宝扫码",
     *   description="返回结果<br>{pic_url:图片信息,query_url:查询接口暂不提供,qrcode_url:唤醒app地址,status:succes,code:1000}<br>message 错误信息",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="username",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="oid",
     *     in="formData",
     *     description="oid",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="money",
     *     in="formData",
     *     description="金额",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="1001",
     *     description="失败"
     *   ),
     *   @SWG\Response(
     *     response="1000",
     *     description="成功"
     *   ),
     *   deprecated=true
     *  )
     */
header("Access-Control-Allow-Origin: *");
ini_set("display_errors", "on");
session_start();
header("content-Type: text/html; charset=utf-8");
require_once("config.php");
$params['site_id'] = SITE_ID;
$params['username'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username',FILTER_SANITIZE_MAGIC_QUOTES));
$params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid',FILTER_SANITIZE_MAGIC_QUOTES);
$params['ulevel'] = CommonClass::filter_input_init(INPUT_POST, 'ulevel',FILTER_SANITIZE_MAGIC_QUOTES);
$params['money'] = CommonClass::filter_input_init(INPUT_POST, 'money',FILTER_SANITIZE_MAGIC_QUOTES);
$params['bank_code'] = 'ZHIFUBAO';
$params['is_zhifubao'] = '1';
//$params['vcode'] = CommonClass::filter_input_init(INPUT_POST, 'vcode',FILTER_SANITIZE_MAGIC_QUOTES);
$params['add_time'] = time();
$params['user_ip'] = $ip;

/*if ($_SESSION['vcode_session'] != $params['vcode'] || empty($params['vcode'])) {//验证码错误
    $re['message'] = ('验证码错误！');
}

unset($_SESSION['vcode_session']);*/
session_destroy();

$re['code'] ='1001';
$re['status'] ='fail';

//信息不全，提交订单失败
if (
        empty($params['money']) ||
        empty($params['username']) ||
        empty($params['oid']) ||
        //empty($params['ulevel']) ||
        !CommonClass::check_money($params['money']) ||
        $params['money'] <= 0
) {
    $re['message'] = ('参数错误，请稍后再试！');
    exit(json_encode($re));
}
$re = $clientA->isLoginUpload($params);

if ($re['code'] != $objCode->is_login_status->code) {//用户已经登出，或异常
    $re['message'] = '用户已经登出';
    exit(json_encode($re));
}
$re['data'] = json_decode($re['info'], TRUE);
//$params['ulevel']=$re['data']['ulevel'];
$limit = $clientA->getSaveLimit(array('site_id' => SITE_ID, 'username'=>$params['username']));
if (!empty($limit) && $params['money'] < $limit['online']['limit_min']) {
    $re['message'] = ("存款金额低于最低存款限制(¥{$limit['online']['limit_min']})！");
    exit(json_encode($re));
}
if (!empty($limit) && $params['money'] > $limit['online']['limit_max']) {
    $re['message'] = ("存款金额高于最高存款限制(¥{$limit['online']['limit_max']})! ");
    exit(json_encode($re));
}
$re = $clientA->saveOnLineOrder($params);
//echo '<pre>';
//print_r($re);
if ($re['code'] == $objCode->success_save_online->code) {//在线存款底单提交成功
    //echo $re['info'];
    $config=CommonClass::getSiteConfig($clientA);
    if(SAVE_TEST){
        $re['info'] = $re['info']."&debug=true";
    }
    $_url_string =gp_curl($re['info']);
    $_url_arr =json_decode($_url_string,true);
    if(isset($_url_arr['status']) && $_url_arr['status']=='success'){
        $_url_arr['qrcode_url'] = isset($_url_arr['qrcode_url']) ? $_url_arr['qrcode_url'] :'';
        $url_arr = array(
            'pic_url'=>$_url_arr['pic_url'],
            'query_url'=>$_url_arr['query_url'],
            'qrcode_url'=>$_url_arr['qrcode_url'],
            'status'=>'success',
            'code'=>'1000'
        );
    }else{
        if(empty($_url_arr) && !empty($_url_string)){
            $url_arr['messege'] = $_url_string;
        }else{
            if(!empty($_url_arr['messege'])){
                $url_arr['messege'] = $_url_arr['messege'];
                if(!empty($_url_arr['error'])){
                    $url_arr['error'] = $_url_arr['error'];
                }
            }else{
                $url_arr['messege'] = '订单生成失败，请稍后重试！';
            }
        }
        $url_arr['status'] = 'fail';
        $url_arr['code'] = '1001';
    }
    exit(json_encode($url_arr));
//    header("Location: {$re['info']}");
}else if($re['code'] == $objCode->is_not_allow_save->code){
    $re['message'] = ("{$objCode->is_not_allow_save->message}");
    exit(json_encode($re));
}else{
    $re['message'] = ('在线入款订单提交失败，请联系客服!');
    exit(json_encode($re));
}
function gp_curl($curl_url, $param=false) {
    $ch = curl_init();
    //echo $curl_url;
    //echo $curl_url.'<br />';
    //使用curl发送和获取数据，可能需要配置php支持，请自行百度
    curl_setopt($ch, CURLOPT_URL, $curl_url);
    if($param){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
    }
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,8);//连接第三方
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);//第三方执行时间
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //将数据传给变量
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); //取消身份验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    $response = curl_exec($ch); //接收返回信息

    if (curl_errno($ch)) {//出错则显示错误信息
//        echo curl_error($ch);
//        echo '<br/>' . 'error';
        return curl_error($ch);
    }

    curl_close($ch); //关闭curl链接
    //print_r($response);

    return $response;
}
