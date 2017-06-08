<?php
    /**
     * @SWG\Post(
     *   path="/saveonline.php",
     *   tags={"存提款"},
     *   summary="在线存款---网银",
     *   description="先调用接口(可判断是否可用):获取在线存款银行列表,提交成功后会返回gurl ,前端需要跳转到改页面进行支付",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名",
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
     *   @SWG\Parameter(
     *     name="bank_code",
     *     in="formData",
     *     description="银行代码（getsaveonlinebanks接口中有）",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="113006",
     *     description="在线入款订单提交失败，请联系客服"
     *   ),
     *   @SWG\Response(
     *     response="103005",
     *     description="在线入款成功"
     *   ),
     *   @SWG\Response(
     *     response="113053",
     *     description="存款不能低于最低限额"
     *   ),
     *   @SWG\Response(
     *     response="113052",
     *     description="存款已经超出最高限额"
     *   ),
     *   @SWG\Response(
     *     response="113056",
     *     description="您存款操作已被停用，请联系客服"
     *   ),
     *   @SWG\Response(
     *     response="113017",
     *     description="请不要重复提交订单"
     *   ),
     *   @SWG\Response(
     *     response="113018",
     *     description="频繁交订单，请稍后再试"
     *   ),
     *  )
     */
//ini_set('display_errors', 'on');
session_start();
header("content-Type: text/html; charset=utf-8");
require_once("config.php");
require_once("Short_hand.php");//common used class by common function
require_once("api_common.php");//common function used in api

unset($_SESSION['vcode_session']);
session_destroy();
$re['code'] ='1001';
$re['status'] ='fail';
//信息不全，提交订单失败
if (isset($_REQUEST['money']))
{
    if (!CommonClass::check_money($_REQUEST['money']) || $_REQUEST['money'] <= 0)
    {
        $re['message'] =  "输入金额错误，请稍后再试！";
        exit(json_encode($re));
    }
}
// get input parameters
$get_input = ['username', 'oid','money','bank_code'];
$params['add_time'] = time();
$params['user_ip'] = $ip;
$re = prepare_goto_hprose($get_input, 'isLoginUpload', $params);
if ($re['code'] != $objCode->is_login_status->code) {//用户已经登出，或异常
    $re['message'] =  "你已登出！";
    exit(json_encode($re));
}
$re['data'] = json_decode($re['info'], TRUE);
$params['ulevel']=$re['data']['ulevel'];
$limit = goto_center_api('getSaveLimit', $params);
/*$limit = $clientA->getSaveLimit(array('site_id' => SITE_ID, 'ulevel' => $params['ulevel'],'username'=>$params['username']));*/
if (!empty($limit) && $params['money'] < $limit['online']['limit_min']) {
    $re['message'] =  "存款金额低于最低存款限制(¥{$limit['online']['limit_min']})！";
    exit(json_encode($re));
}
if (!empty($limit) && $params['money'] > $limit['online']['limit_max']) {
    $re['message'] =  "存款金额高于最高存款限制(¥{$limit['online']['limit_max']})！";
    exit(json_encode($re));
}
$re = goto_center_api('saveOnLineOrder', $params);
if ($re['code'] == $objCode->success_save_online->code) {//在线存款底单提交成功
    $config=CommonClass::getSiteConfig($clientA);
    if(SAVE_TEST){
        $re['info'] = $re['info']."&debug=true";
    }
        ob_clean();
        exit(json_encode([
            'status'=>'success',
            'code'=>'10000',
            'url'=>$re['info'],
            'payname'=>$re['payname'],
            'billno'=>$re['billno']
        ]));
}else if($re['code'] == $objCode->is_not_allow_save->code){
    $re['message'] =  "{$objCode->is_not_allow_save->message}";
    exit(json_encode($re));
}
else{
    exit(json_encode($re));
}