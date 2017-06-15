<?php
//error_reporting(0);
header("Access-Control-Allow-Origin: *");
ini_set("display_errors", "off");
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
    $PINGTAI_URL = POINT_URL;//新转账接口试玩地址
} else {
    $PINGTAI_URL = POINT_URL;//新转账接口正式地址;
}
$return = [];
switch ($action) {
    /**
     * @SWG\Post(
     *   path="/point.php?action=balance",
     *   tags={"用户信息"},
     *   summary="获取积分",
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
     *     response="1000",
     *     description="成功！"
     *   ),
     *   @SWG\Response(
     *     response="1001",
     *     description="出现错误！"
     *   )
     * )
     */
    case 'balance':
        $f = new Fetch($PINGTAI_URL);
        $paramsb = [
            'siteId' => SITE_ID,
            'username' => $params['username'],
            'key' => ''
        ];
        $paramsb = http_build_query($paramsb);
        $res = $f->PointCheckUsrBalance($paramsb);
        $result = json_decode($res, TRUE);
        $return = $result['status'] == '100000' ? [
            'error' => '1000',
            'code' => '1000',
            'data' => [
                'point' => substr(sprintf("%.5f", $result['message']['point']), 0, -3)
            ]
        ] : [
            'code' => '1001',
            'errMsg' => '积分查询失败！',
        ];
        if ($result['status']!= '100000')
        {
            //####################################################################
            $log_to_write = [
                'errMsg' => $result['errMsg'],
                'params'=>$paramsb,
                'code' => '1001',
                'message' => '积分查询失败！',
                'data' => json_encode($result, JSON_UNESCAPED_UNICODE),
                'url' => $PINGTAI_URL . 'getPoint',
                'level' => $action
            ];
            $log_write = log_args_write($log_to_write);
            //#####################################################################
        }
        break;
    /**
     * @SWG\Post(
     *   path="/point.php?action=sys800",
     *   tags={"报表日志"},
     *   summary="积分流",
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
     *     description="选择类型(1: 2: 3： 不填：所有类型)",
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
     *     response="1001",
     *     description="获取现金流日志失败！"
     *   ),
     *   @SWG\Response(
     *     response="1000",
     *     description="获取现金流日志成功！"
     *   )
     * )
     */
    case 'sys800':
        $get_input = ['beginTime', 'endTime'];
        $neglegible = $get_input;
        $params['key'] = '';
        $type = $_REQUEST['type'];
        if ($type == 1) {
            $type = '70002,';
            for ($i = 10001; $i <= 10067; $i++) {
                $type .= $i . ',';
            }
            $type = trim($type, ',');
        } else if ($type == 2) {
            $type = '60000,60001,20013,20014,';
            for ($i = 20001; $i <= 20009; $i++) {
                $type .= $i . ',';
            }
            $type = trim($type, ',');
        } else if ($type == 3) {
            $type = '2000,2001,50001,50002,70000,70001,';
            for ($i = 30001; $i <= 30012; $i++) {
                $type .= $i . ',';
            }
            $type = trim($type, ',');
        }
        $params['type'] = $type;//待前端修复参数名// $paramsb['fromKeyType'] = $fromKeyType;
        $params['page'] = empty($_REQUEST['page'])? 1 : $_REQUEST['page'];
        $params['pageSize'] = empty($_REQUEST['pageSize'])? 5 : $_REQUEST['pageSize'];
        $res = prepare_goto_hprose($get_input, 'getPointLog', $params, $neglegible);//$res = $clientA->getPointLog($params); //
        if ($res['error'] == '1000') {
            $ret = json_decode($res['data'], true);
            $return['data'] = [];
            if ($ret['data']) {
                foreach ($ret['data'] as $i => &$v) {
                    $return['data'][$i]['mtime'] = date("Y-m-d H:i:s", $v['createTime'] / 1000);
                    $return['data'][$i]['mtype'] = isset($formKeyType[$v['fromKeyType']]) ? $formKeyType[$v['fromKeyType']] : '未知类型';
                    $return['data'][$i]['mnowmoney'] = sprintf("%.4f", $v['afterPoint']);
                    $v['transType'] = strtolower($v['transType']);
                    if ($v['transType'] == 'in') {
                        $return['data'][$i]['mgold'] = sprintf("%.4f", $v['remit']);
                    } else {
                        $return['data'][$i]['mgold'] = '<span style="color:red;"> -' . sprintf("%.4f", $v['remit']) . '</span>';
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
            $return['error'] = 1000;
            $return['code'] = 1000;
            $return['re'] = $ret;
            $return['pra'] = $params;//$paramsb;
        } else {
            $return = ['error' => '1001', 'code' => '1001'];
        }
        break;
}
echo CommonClass::ajax_return($return, $jsonp, $jsonpcallback);
