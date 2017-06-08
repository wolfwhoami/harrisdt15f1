<?php
//error_reporting(0);
header("Access-Control-Allow-Origin: *");
ini_set("display_errors", "on");
require_once("config.php");
require_once("Fetch.class.php");
require_once("Short_hand.php");//common used class by common function
require_once("api_common.php");//common function used in api

$get_input = ['username', 'oid'];
$re = prepare_goto_hprose($get_input, 'isLogin', $params);
if ($re['code'] != $objCode->is_login_status->code) {//用户已经登出，或异常
//    unset($re['info']);
    $re = [
        'code'=> $objCode->is_not_login_status->code,
        'errMsg'=>'请先登录'
    ];
    echo json_encode($re);
    die();
}
$config = CommonClass::getSiteConfig($clientA);
$session = json_decode($re['info'], TRUE);
if (substr($session['user_type'], -1) == 1) {//主副站修改
    $PINGTAI_URL = NEW_BALANCE_URL_TEST;//新转账接口试玩地址
//    $AG_PREFIX = AG_PREFIX_TEST;
//    $BBIN_PREFIX = BBIN_PREFIX_TEST;
//    $DS_PREFIX = DS_PREFIX_TEST;
//    $H8_PREFIX = H8_PREFIX_TEST;
//    
//    $AG_HASHCODE = AG_HASHCODE_TEST;
//    $BBIN_HASHCODE = BBIN_HASHCODE_TEST;
//    $DS_HASHCODE = DS_HASHCODE_TEST;
//    $H8_HASHCODE = H8_HASHCODE_TEST;
    $MY_HTTP_MONEY_HOST = MY_HTTP_MONEY_HOST_TEST;
    $MY_TCP_MONEY_HOST = MY_TCP_MONEY_HOST_TEST;
} else {
    $PINGTAI_URL = NEW_BALANCE_URL;//新转账接口正式地址;
//    $AG_PREFIX = AG_PREFIX;
//    $BBIN_PREFIX = BBIN_PREFIX;
//    $DS_PREFIX = DS_PREFIX;
//    $H8_PREFIX = H8_PREFIX;
//    
//    $AG_HASHCODE = AG_HASHCODE;
//    $BBIN_HASHCODE = BBIN_HASHCODE;
//    $DS_HASHCODE = DS_HASHCODE;
//    $H8_HASHCODE = H8_HASHCODE;
    $MY_HTTP_MONEY_HOST = MY_HTTP_MONEY_HOST;
    $MY_TCP_MONEY_HOST = MY_TCP_MONEY_HOST;
}
/*新增$PINGTAI_URL*/
$AG_LIVE_TYPE = '2';
$MG_LIVE_TYPE = '15';
$BBIN_LIVE_TYPE = '11';
$H8_LIVE_TYPE = '13';
$OG_LIVE_TYPE = '3';

$AG_BBIN_LIVE_TYPE = '2_11';
$AG_H8_LIVE_TYPE = '2_13';
$AG_MG_LIVE_TYPE = '2_15';
$BBIN_H8_LIVE_TYPE = '11_13';
$BBIN_MG_LIVE_TYPE = '11_15';
$H8_MG_LIVE_TYPE = '13_15';

$AG_OG_LIVE_TYPE = '2_3';
$BBIN_OG_LIVE_TYPE = '11_3';
$KEYB = NEW_KEYB;


$transfer = [
    'ag' => $AG_LIVE_TYPE,
    'mg' => $MG_LIVE_TYPE,
    'bin' => $BBIN_LIVE_TYPE,
    'h8' => $H8_LIVE_TYPE,
    'og' => $OG_LIVE_TYPE,
];
$transfer_succes = [
    'ag' => 103121,
    'bin' => 103122,
    'h8' => 103123,
    'og' => 103123,
    'mg' => 103124,
];
$transfer_fail = [
    'ag' => 113125,
    'bin' => 113126,
    'h8' => 113127,
    'og' => 113126,
    'mg' => 113128,
];
/**
 * @SWG\Post(
 *   path="/balance.php?action=ag",
 *   tags={"余额"},
 *   summary="ag平台余额查询",
 *   description="",
 *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
 *   produces={"application/json"},
 *   @SWG\Parameter(
 *     name="oid",
 *     in="formData",
 *     description="oid",
 *     required=true,
 *     type="string",
 *   ),
 *   @SWG\Parameter(
 *     name="username",
 *     in="formData",
 *     description="用户名",
 *     required=true,
 *     type="string",
 *   ),
 *   @SWG\Response(
 *     response="103121",
 *     description="获取AG余额成功"
 *   ),
 *   @SWG\Response(
 *     response="113125",
 *     description="获取AG余额失败"
 *   ),
 *   @SWG\Response(
 *     response="211014",
 *     description="未登录！"
 *   )
 * )
 */


/**
 * @SWG\Post(
 *   path="/balance.php?action=mg",
 *   tags={"余额"},
 *   summary="mg平台余额查询",
 *   description="",
 *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
 *   produces={"application/json"},
 *   @SWG\Parameter(
 *     name="oid",
 *     in="formData",
 *     description="oid",
 *     required=true,
 *     type="string",
 *   ),
 *   @SWG\Parameter(
 *     name="username",
 *     in="formData",
 *     description="用户名",
 *     required=true,
 *     type="string",
 *   ),
 *   @SWG\Response(
 *     response="103124",
 *     description="获取MG余额成功"
 *   ),
 *   @SWG\Response(
 *     response="113128",
 *     description="获取MG余额失败"
 *   ),
 *   @SWG\Response(
 *     response="211014",
 *     description="未登录！"
 *   )
 * )
 */
if (array_key_exists($action, $transfer)) {
    $f = new Fetch($PINGTAI_URL);
    $p = [
        'siteId' => SITE_ID,
        'username' => $params['username'],
        'live' => $transfer[$action],
        'key' => CommonClass::get_key_param($params['username'] . $KEYB . date("Ymd"), 4, 1)
    ];
//        print_r($p);
//        $f->debug = TRUE;

    $p = http_build_query($p);
    $data = $f->NewCheckUsrBalance($p);
    if (strstr($data, 'status') === FALSE || strstr($data, 'message') === FALSE) {
    }//判断是否连通
    $sucess_code = $transfer_succes[$action];
    $fail_code = $transfer_fail[$action];
    if ($data) {
        $re = json_decode($data, TRUE);

        $return = $re['status'] == '10000' ? [
            'code' => $sucess_code,
            'data' => [
                'money' => substr(sprintf("%.5f", $re['balance']), 0, -3)
            ]
        ] : [
            'code' => $fail_code,
            'data' => $re,
            'p' => $p,
            'url' => $PINGTAI_URL
        ];
    } else {
//            $return = array('code' => $objCode->fail_to_get_ag_money->code);
        $return = [
            'code' => $fail_code,
            'data' => $data,
            'p' => $p,
            'url' => $PINGTAI_URL
        ];
    }
}
switch ($action) {
    /**
     * @SWG\Post(
     *   path="/balance.php?action=main",
     *   tags={"余额"},
     *   summary="主余额查询",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="oid",
     *     in="formData",
     *     description="oid",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Response(
     *     response="103001",
     *     description="获取主余额成功"
     *   ),
     *   @SWG\Response(
     *     response="113124",
     *     description="获取主余额失败"
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="未登录！"
     *   )
     * )
     */
    case 'main':
        //$clientB = new HproseSwooleClient($MY_TCP_MONEY_HOST); //光光
        //define('MY_HTTP_MONEY_HOST', 'http://119.9.70.177:18080/kg-money-api/');
        $f = new Fetch($MY_HTTP_MONEY_HOST . 'getMoney');
        $key = CommonClass::get_key_param(SITE_WEB . $params['username'], 5, 6);
        $paramsb = [
            'fromKey' => SITE_WEB,
            'siteId' => SITE_ID,
            'username' => $params['username'],
            'key' => $key
        ];
        //$res = $clientB->getMoney(json_encode($paramsb));
        //$f->debug=true;
        $res = $f->CheckUsrBalance($paramsb);

        $result = json_decode($res, TRUE);
        $return = $result['code'] == '100000' ? [
            'code' => $objCode->success_to_get_main_money->code,
            'data' => [
                'money' => substr(sprintf("%.5f", $result['data']['money']), 0, -3)
            ]
        ] : [
            'code' => $objCode->fail_to_get_main_money->code,
            'data' => $result,
            'url' => $MY_HTTP_MONEY_HOST . 'getMoney'
        ];
        break;
    case 'ds':
        $return = [
            'code' => $objCode->success_to_get_ds_money->code,
            'data' => ['money' => 103.25]
        ];
        break;
    case 'xyb'://获取有效幸运币
        $params['username'] = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['company'] = SITE_ID;

        $str = $params['company'] . $params['username'] . date("Y-m-d") . date("Y-m-d") . '00:00:00' . '23:59:59';
        $data = [
            'siteId' => $params['company'],
            'username' => $params['username'],
            'betTimeBegin' => date("Y-m-d"),
            'betTimeEnd' => date("Y-m-d"),
            'startTime' => '00:00:00',
            'endTime' => '23:59:59',
            'key' => CommonClass::get_key_param($str, 5, 6)
        ];
        $result1 = $clientB->auditTotalTemp(json_encode($data));
        $result2 = str_replace([
            '"[',
            ']"',
            '\"',
            '\\\\',
            '"{',
            '}"'
        ], [
            '[',
            ']',
            '"',
            '\\',
            '{',
            '}'
        ], $result1);
        $result = json_decode($result2, TRUE);
        $xyb = 0;
        if ($result['returnCode'] == '900000') {
            $allcode = isset($result['dataList']['totalValidamount']) ? $result['dataList']['totalValidamount'] : 0; //实际投注，总投注
            $allbi = round($allcode * XYB_RATE);
            if ($allbi > 0) {
                $xf = $clientA->countUsedXingyunBi($params);//已消费的幸运币
            }
            $xyb = $allbi - $xf;//有效幸运币
        }
        echo ($xyb >= 0) ? $xyb : 0;
        break;
    case 'all'://查询所有月
        $clientB = new HproseSwooleClient($MY_TCP_MONEY_HOST); //光光
        $key = CommonClass::get_key_param(SITE_WEB . $params['username'], 5, 6);
        $paramsb = [
            'fromKey' => SITE_WEB,
            'siteId' => SITE_ID,
            'username' => $params['username'],
            'key' => $key
        ];
        $res = $clientB->getMoney(json_encode($paramsb));
        $result = json_decode($res, TRUE);
        $mainmoney = 0;
        if ($result['code'] == '100000') {
            $mainmoney = sprintf("%.2f", $result['data']['money']);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/balance.php?action=changemoney",
     *   tags={"余额"},
     *   summary="转账",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="oid",
     *     in="formData",
     *     description="oid",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="cout",
     *     in="formData",
     *     description="从哪里转出(1 主余额 2 ag 5 mg)",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="cin",
     *     in="formData",
     *     description="转到哪里(1 主余额 2 ag 5 mg)",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="money",
     *     in="formData",
     *     description="转账金额",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Response(
     *     response="103019",
     *     description="转账成功"
     *   ),
     *   @SWG\Response(
     *     response="113020",
     *     description="转账失败，请稍后再试"
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="未登录！"
     *   )
     * )
     */
    case 'changemoney':
        $params['site_id'] = SITE_ID;
        $params['cout'] = $cout = CommonClass::filter_input_init(INPUT_POST, 'cout', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['cin'] = $cin = CommonClass::filter_input_init(INPUT_POST, 'cin', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['money'] = $money = CommonClass::filter_input_init(INPUT_POST, 'money', FILTER_SANITIZE_MAGIC_QUOTES);
        if (
            $cout == $cin ||
            ($cout != 1 && $cout != 2 && $cout != 3 && $cout != 4 && $cout != 5 && $cout != 12) ||
            ($cin != 1 && $cin != 2 && $cin != 3 && $cin != 4 && $cin != 5 && $cin != 34) ||
            !CommonClass::check_money($money) ||
            $money <= 0
        ) {
            $return = [
                'code' => $objCode->fail_change_money->code
            ];
            break;
        }
        unset($params['oid']);
        $re = $clientA->addChangeMoney($params); //添加转账注单
        if ($re['code'] == $objCode->success_change_money->code) {
            $billno = $re['info'];

            //开始调取转账接口
            $ct = '' . $cout . $cin;
//            $ar['action'] = 'transfer';
            $ar['billno'] = $billno;
            $ar['credit'] = $money;
            $ar['siteId'] = SITE_ID;
            $ar['operator'] = $params['username'];
            $ar['username'] = $params['username'];
            $ar['key'] = CommonClass::get_key_param($params['username'] . $KEYB . date("Ymd"), 4, 1);
//            $ar['fromKey'] = SITE_WEB;
            //$ar['uppername'] = UP_NAME;
            switch ($ct) {
                case '12':
                    //从DS主账户转到ag
                    $ar['type'] = 'IN';
                    $ar['live'] = $AG_LIVE_TYPE;
                    $ar['transMethod'] = 'ag';
                    //$ar['fromKeyType'] = '20002';
                    //$ar['fromKeyType'] = '20002';
                    break;
                case '13':
                    //从DS主账户转到bbin
                    $ar['type'] = 'IN';
                    $ar['live'] = $BBIN_LIVE_TYPE;
                    $ar['transMethod'] = 'bbin';
                    //$ar['fromKeyType'] = '20001';
//                    $ar['fromKeyType'] = '2000';
                    break;
                case '14':
                    //从DS主账户转到h8
                    $ar['type'] = 'IN';
                    $ar['live'] = $H8_LIVE_TYPE;
                    $ar['transMethod'] = 'h8';
                    //从DS主账户转到og
//                    $ar['type'] = 'IN';
//                    $ar['live'] = $OG_LIVE_TYPE;
//                    $ar['transMethod']='og';

                    //$ar['fromKeyType'] = '20003';
                    //$ar['fromKeyType'] = '2000';
                    break;
                case '15':
                    //从DS主账户转到MG
                    $ar['type'] = 'IN';
                    $ar['live'] = $MG_LIVE_TYPE;
                    $ar['transMethod'] = 'mg';
                    break;
                case '21':
                    //从ag转到DS主账户
                    $ar['type'] = 'OUT';
                    $ar['live'] = $AG_LIVE_TYPE;
                    $ar['transMethod'] = 'ag';
                    //$ar['fromKeyType'] = '20005';
                    //$ar['fromKeyType'] = '2000';
                    break;
                case '23':
                    //从ag转到bbin
                    $ar['type'] = 'IN';
                    $ar['live'] = $AG_BBIN_LIVE_TYPE;
                    $ar['transMethod'] = 'ag_bbin';
                    //$ar['fromKeyType'] = '20005';
                    //$ar['fromKeyType'] = '2000';
                    break;
                case '24':
                    //从ag转到H8
                    $ar['type'] = 'IN';
                    $ar['live'] = $AG_H8_LIVE_TYPE;
                    $ar['transMethod'] = 'ag_h8';
//                  //从ag转到og
//                    $ar['type'] = 'IN';
//                    $ar['live'] = $AG_OG_LIVE_TYPE;
//                    $ar['transMethod']='ag_og';

                    //$ar['fromKeyType'] = '20005';
                    //$ar['fromKeyType'] = '2000';
                    break;
                case '25':
                    //从ag转到mg
                    $ar['type'] = 'IN';
                    $ar['live'] = $AG_MG_LIVE_TYPE;
                    $ar['transMethod'] = 'ag_mg';
                    break;
                case '31':
                    //从bbin转到DS主账户
                    $ar['type'] = 'OUT';
                    $ar['live'] = $BBIN_LIVE_TYPE;
                    $ar['transMethod'] = 'bbin';
                    //$ar['fromKeyType'] = '20004';
                    //$ar['fromKeyType'] = '2000';
                    break;
                case '32':
                    //从bbin转到ag
                    $ar['type'] = 'OUT';
                    $ar['live'] = $AG_BBIN_LIVE_TYPE;
                    $ar['transMethod'] = 'ag_bbin';
                    //$ar['fromKeyType'] = '20004';
                    //$ar['fromKeyType'] = '2000';
                    break;
                case '34':
                    //从bbin转到h8
                    $ar['type'] = 'IN';
                    $ar['live'] = $BBIN_H8_LIVE_TYPE;
                    $ar['transMethod'] = 'bbin_h8';

                    //从bbin转到og
//                    $ar['type'] = 'IN';
//                    $ar['live'] = $BBIN_OG_LIVE_TYPE;
//                    $ar['transMethod']='bbin_og';


                    //$ar['fromKeyType'] = '20004';
                    //$ar['fromKeyType'] = '2000';
                    break;
                case '35':
                    //从bbin转到mg
                    $ar['type'] = 'IN';
                    $ar['live'] = $BBIN_MG_LIVE_TYPE;
                    $ar['transMethod'] = 'bbin_mg';
                    break;
                case '41':
                    //从h8转到main
                    $ar['type'] = 'OUT';
                    $ar['live'] = $H8_LIVE_TYPE;
                    $ar['transMethod'] = 'h8';

                    //从og转到main
//                    $ar['type'] = 'OUT';
//                    $ar['live'] = $OG_LIVE_TYPE;
//                    $ar['transMethod']='og';

                    //$ar['fromKeyType'] = '20004';
                    //$ar['fromKeyType'] = '2000';
                    break;
                case '42':
                    //从h8转到ag
                    $ar['type'] = 'OUT';
                    $ar['live'] = $AG_H8_LIVE_TYPE;
                    $ar['transMethod'] = 'ag_h8';

                    //从og转到ag
//                    $ar['type'] = 'OUT';
//                    $ar['live'] = $AG_OG_LIVE_TYPE;
//                    $ar['transMethod']='ag_og';

                    //$ar['fromKeyType'] = '20004';
                    //$ar['fromKeyType'] = '2000';
                    break;
                case '43':
                    //从h8转到bbin
                    $ar['type'] = 'OUT';
                    $ar['live'] = $BBIN_H8_LIVE_TYPE;
                    $ar['transMethod'] = 'bbin_h8';


                    //从og转到bbin
//                    $ar['type'] = 'OUT';
//                    $ar['live'] = $BBIN_OG_LIVE_TYPE;
//                    $ar['transMethod']='bbin_og';

                    //$ar['fromKeyType'] = '20004';
                    //$ar['fromKeyType'] = '2000';
                    break;
                case '45':
                    //从h8转到mg
                    $ar['type'] = 'IN';
                    $ar['live'] = $H8_MG_LIVE_TYPE;
                    $ar['transMethod'] = 'h8_mg';
                    break;
                case '51':
                    //从mg转到main
                    $ar['type'] = 'OUT';
                    $ar['live'] = $MG_LIVE_TYPE;
                    $ar['transMethod'] = 'mg';
                    break;
                case '52':
                    //从mg转到ag
                    $ar['type'] = 'OUT';
                    $ar['live'] = $AG_MG_LIVE_TYPE;
                    $ar['transMethod'] = 'ag_mg';
                    break;
                case '53':
                    //从mg转到bbin
                    $ar['type'] = 'OUT';
                    $ar['live'] = $BBIN_MG_LIVE_TYPE;
                    $ar['transMethod'] = 'bbin_mg';
                    break;
                case '54':
                    //从mg转到h8
                    $ar['type'] = 'OUT';
                    $ar['live'] = $H8_MG_LIVE_TYPE;
                    $ar['transMethod'] = 'h8_mg';
                    break;
                case '1234':
                    //资金归集
//                    $ar['type'] = 'OUT';
                    //$ar['fromKeyType'] = '2000';
                    $ar['live'] = '99999';
                    $ar['transMethod'] = 'balanceTotal';
                    unset($ar['credit']);
                    break;
            }
            if ($ar['key']) {
                $f = new Fetch($PINGTAI_URL);
//                $f->debug=TRUE;
                $p = http_build_query($ar);

                $data = $f->NewTransfer($p);
                if (strstr($data, 'status') === FALSE || strstr($data, 'message') === FALSE || empty($data)) {
                    print_r($data);
                    exit;
                }//判断是否连通

            }
            if ($data) {
                $re = json_decode($data, TRUE);
                $return = $re['status'] == '10000' ? [
                    'code' => $objCode->success_change_money->code
                ] : [
                    'code' => $objCode->fail_change_money->code,
                    'error' => json_encode($data)
                ];
            } else {
                $return = [
                    'code' => $objCode->fail_change_money->code,
                    'error' => json_encode($data)
                ];
            }
        } else {
            $return = [
                'code' => $objCode->fail_change_money->code,
                'error' => 'error'
            ];
            break;
        }
        break;
    /**
     * @SWG\Post(
     *   path="/balance.php?action=sys800",
     *   tags={"报表日志"},
     *   summary="资金交易",
     *   description="返回结果<br>{page(当前页): 1, pageSize: 8, totalNumber(总条数): 14, allPage（总页数）: 2}",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="oid",
     *     in="formData",
     *     description="oid",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="fromKeyType",
     *     in="formData",
     *     description="选择类型(1:存/提款记录 2:转账记录 3：返水 不填：所有类型)",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="beginTime",
     *     in="formData",
     *     description="查询开始日期",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="endTime",
     *     in="formData",
     *     description="查询结束日期",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="page",
     *     in="formData",
     *     description="页码",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="pageSize",
     *     in="formData",
     *     description="每页条数",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="113046",
     *     description="获取现金流日志失败！"
     *   ),
     *   @SWG\Response(
     *     response="103045",
     *     description="获取现金流日志成功！"
     *   )
     * )
     */
    case 'sys800':
        $clientB = new HproseSwooleClient($MY_TCP_MONEY_HOST); //光光
        $beginTime = CommonClass::filter_input_init(INPUT_POST, 'beginTime', FILTER_SANITIZE_MAGIC_QUOTES);
        $endTime = CommonClass::filter_input_init(INPUT_POST, 'endTime', FILTER_SANITIZE_MAGIC_QUOTES);
        $fromKeyType = CommonClass::filter_input_init(INPUT_POST, 'fromKeyType', FILTER_SANITIZE_MAGIC_QUOTES);
        if ($fromKeyType == 1) {//存提款
            for ($i = 10001; $i <= 10054; $i++) {
                $fromKeyType .= $i . ',';
            }
            $fromKeyType = trim($fromKeyType, ',');
        } else if ($fromKeyType == 2) {//转账
            for ($i = 20001; $i <= 20014; $i++) {
                $fromKeyType .= $i . ',';
            }
            $fromKeyType = trim($fromKeyType, ',');
        } else if ($fromKeyType == 3) {//返水
            $fromKeyType = '70002,';
            for ($i = 10060; $i <= 10067; $i++) {
                $fromKeyType .= $i . ',';
            }
            $fromKeyType = trim($fromKeyType, ',');
        }
        $page = CommonClass::filter_input_init(INPUT_POST, 'page', FILTER_SANITIZE_MAGIC_QUOTES);
        $pageSize = CommonClass::filter_input_init(INPUT_POST, 'pageSize', FILTER_SANITIZE_MAGIC_QUOTES);
        $key = CommonClass::get_key_param(SITE_WEB . $params['username'], 5, 6);
        $paramsb = [
            'fromKey' => SITE_WEB,
            'siteId' => SITE_ID,
            'username' => $params['username'],
            'key' => $key
        ];
        if (!empty($beginTime)) {
            $paramsb['beginTime'] = $beginTime;
        }
        if (!empty($endTime)) {
            $paramsb['endTime'] = $endTime;
        }
        if (!empty($fromKeyType)) {
            $paramsb['fromKeyType'] = $fromKeyType;
        } else {
            $fromKeyType = '';
            for ($i = 10001; $i <= 10052; $i++) {
                $fromKeyType .= $i . ',';
            }
            for ($i = 20001; $i <= 20009; $i++) {
                $fromKeyType .= $i . ',';
            }
            $fromKeyType = trim($fromKeyType, ',');
        }
        if (!empty($page)) {
            $paramsb['page'] = $page;
        }
        if (!empty($pageSize)) {
            $paramsb['pageSize'] = $pageSize;
        }
        $paramsb['userInfoIsDetail'] = 1;
        //print_r($paramsb);
        $res = $clientB->memberMoneyLog(json_encode($paramsb));
        $ret = json_decode($res, TRUE);
        if ($ret['code'] == '100000') {
            $return['data'] = [];
            if ($ret['data']) {
                foreach ($ret['data'] as $i => &$v) {
                    $return['data'][$i]['mtime'] = date("Y-m-d H:i:s", $v['createTime'] / 1000);
                    $return['data'][$i]['mtype'] = isset($formKeyType[$v['fromKeyType']]) ? $formKeyType[$v['fromKeyType']] : '测试数据';
                    $return['data'][$i]['mnowmoney'] = sprintf("%.2f", $v['afterMoney']);
                    $v['transType'] = strtolower($v['transType']);
                    if ($v['transType'] == 'in') {
                        $return['data'][$i]['mgold'] = sprintf("%.2f", $v['remit']);
                    } else {
                        $return['data'][$i]['mgold'] = '<span style="color:red;"> -' . sprintf("%.2f", $v['remit']) . '</span>';
                    }
                    $return['data'][$i]['mnote'] = $v['memo'];
                    if (strstr($v['memo'], '操作者')) {
                        $mn = explode('(', $v['memo']);
                        if (isset($mn[1])) {
                            $return['data'][$i]['mnote'] = substr($mn[1], 0, -1);
                        }
                    }
                    $return['data'][$i]['billno'] = $v['transId'];
                }
            }
            if (($ret['pagation']['totalNumber'] % $ret['pagation']['pageSize']) != 0) {
                $ret['pagation']['allPage'] = intval(floor($ret['pagation']['totalNumber'] / $ret['pagation']['pageSize'])) + 1;
            } else {
                $ret['pagation']['allPage'] = intval(floor($ret['pagation']['totalNumber'] / $ret['pagation']['pageSize']));
            }
            $return['pagation'] = $ret['pagation'];
            $return['code'] = $objCode->success_get_money_logs->code;
            $return['re'] = $ret;
            $return['pra'] = $paramsb;
        } else {
            $return = [
                'code' => $objCode->fail_get_money_logs->code
            ];
        }
        break;
    //返水
    /**
     * @SWG\Post(
     *   path="/balance.php?action=spread",
     *   tags={"报表日志"},
     *   summary="返水记录",
     *   description="返回结果<br>{page(当前页): 1, pageSize: 8, totalNumber(总条数): 14, allPage（总页数）: 2}",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="oid",
     *     in="formData",
     *     description="oid",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="beginTime",
     *     in="formData",
     *     description="查询开始日期",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="endTime",
     *     in="formData",
     *     description="查询结束日期",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="page",
     *     in="formData",
     *     description="页码",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="pageSize",
     *     in="formData",
     *     description="每页条数",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="1001",
     *     description="获取返水记录失败！"
     *   ),
     *   @SWG\Response(
     *     response="1000",
     *     description="获取返水记录成功！"
     *   )
     * )
     */
    case 'spread':
        $beginTime = CommonClass::filter_input_init(INPUT_POST, 'beginTime', FILTER_SANITIZE_MAGIC_QUOTES);
        $endTime = CommonClass::filter_input_init(INPUT_POST, 'endTime', FILTER_SANITIZE_MAGIC_QUOTES);
        $page = CommonClass::filter_input_init(INPUT_POST, 'page', FILTER_SANITIZE_MAGIC_QUOTES);
        $pageSize = CommonClass::filter_input_init(INPUT_POST, 'pageSize', FILTER_SANITIZE_MAGIC_QUOTES);
        $key = CommonClass::get_key_param(SITE_WEB . $params['username'], 5, 6);
        $paramsb = [
            'siteId' => SITE_ID,
            'username' => $params['username'],
        ];
        if (!empty($beginTime)) {
            $paramsb['fromDate'] = $beginTime;
        }
        if (!empty($endTime)) {
            $paramsb['toDate'] = $endTime;
        }

        if (!empty($page)) {
            $paramsb['pageNumber'] = $page;
        }
        if (!empty($pageSize)) {
            $paramsb['pageSize'] = $pageSize;
        }
        $res = $clientA->lossBonusLog($paramsb);
        $ret = $res;
        if ($ret['code'] == '1000') {
            $return['data'] = [];
            if ($ret['data']) {
                foreach ($ret['data'] as $i => &$v) {
                    $return['data'][$i]['mtime'] = date("Y-m-d H:i:s", $v['createTime'] / 1000);
                    $return['data'][$i]['lossMoney'] = sprintf("%.2f", $v['lossMoney']);
                    $return['data'][$i]['lossMoneyBonus'] = sprintf("%.2f", $v['lossMoneyBonus']);
                    $return['data'][$i]['state'] = $v['state'];
                }
            }
            if (($ret['pagation']['totalNumber'] % $ret['pagation']['pageSize']) != 0) {
                $ret['pagation']['allPage'] = intval(floor($ret['pagation']['totalNumber'] / $ret['pagation']['pageSize'])) + 1;
            } else {
                $ret['pagation']['allPage'] = intval(floor($ret['pagation']['totalNumber'] / $ret['pagation']['pageSize']));
            }
            $return['pagation'] = $ret['pagation'];
            $return['code'] = $ret['code'];
            $return['re'] = $ret;
            $return['pra'] = $paramsb;
        } else {
            $return = [
                'code' => $ret['code'],
                'error' => $res
            ];
        }
        break;
}
echo CommonClass::ajax_return($return, $jsonp, $jsonpcallback);

