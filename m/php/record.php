<?php

ini_set("display_errors", "on");
header("Access-Control-Allow-Origin: *");
require_once("config.php");
require_once("Short_hand.php");//common used class by common function
require_once("api_common.php");//common function used in api
//$clientB = new HproseSwooleClient(MY_TCP_CENTER_HOST); //小温
session_start();
switch ($action) {
    /**
     * @SWG\Post(
     *   path="/record.php?action=getgametype",
     *   tags={"公共信息"},
     *   summary="获取游戏类型",
     *   description="返回参数<br>out_game_code游戏大类fk_live_id对应视讯来源",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response="302009",
     *     description="获取游戏类型成功"
     *   ),
     *   @SWG\Response(
     *     response="312010",
     *     description="获取游戏类型失败"
     *   )
     * )
     */
    case 'getgametype':
        $re = $clientA->getGameType();

        if ($re['code'] == $objCode->success_get_game_type->code) {
            $re['data'] = ['gametype' => json_decode($re['info'])];
        }
        unset($re['info']);
        echo CommonClass::ajax_return($re, $jsonp, $jsonpcallback);
        break;
    /**
     * @SWG\Post(
     *   path="/record.php?action=getrecord",
     *   tags={"报表日志"},
     *   summary="分类获取投注记录",
     *   description="请求参数<br>：0__(双下划线)80__(双下划线)22(80 游戏类型id,22 live_id)",
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
     *     name="clas",
     *     in="formData",
     *     description="",
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
    case 'getrecord'://TODO 验证登录
        $class = CommonClass::filter_input_init(INPUT_POST, 'clas', FILTER_SANITIZE_MAGIC_QUOTES);
        //$time = CommonClass::filter_input_init(INPUT_POST, 'game_time');
        $username = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        $cl = explode("__", $class);
        $siteid = SITE_ID;
        $liveid = str_replace(["+", "active", " "], ["", "", ""], $cl[2]);
        $gameKind = $cl[1];
        $betTimeBegin = date("Y-m-d");
        $betTimeEnd = date("Y-m-d");
        $starTime = "00:00:00";
        $endTime = "23:59:59";
        $str = $siteid . $username . $liveid . $gameKind . $betTimeBegin . $betTimeEnd . $starTime . $endTime;
        $key = "dsasf" . md5($str) . 'dserft';
        $params = [
            'siteId' => $siteid,
            'username' => $username,
            'liveId' => $liveid,
            'gameKind' => $gameKind,
            'betTimeBegin' => $betTimeBegin,
            'betTimeEnd' => $betTimeEnd,
            'startTime' => $starTime,
            'endTime' => $endTime,
            'key' => $key
        ];
        /*        if($username == 'linux' || $username=='admin'){
          echo "<pre>";
          echo MY_TCP_CENTER_HOST.'<br/>';
          print_r($params);
          echo "</pre>";
          } */
        $config = CommonClass::getSiteConfig($clientA);
        $clientB = new HproseSwooleClient(MY_TCP_CENTER_HOST); //小温
        $result = $clientB->betTotalByUser(json_encode($params));
        $result = str_replace(['"[', ']"', '\"', '\\\\', '"{', '}"'], ['[', ']', '"', '\\', '{', '}'], $result);
        $res = json_decode($result, true);
        if (isset($res['returnCode']) && $res['returnCode'] == '900000') {
            $res['code'] = '1000';
        } else {
            $res['code'] = '1001';
            $res['message'] = $result;
        }
        echo json_encode($res);
        break;
    /**
     * @SWG\Post(
     *   path="/record.php?action=getrecorddetail",
     *   tags={"报表日志"},
     *   summary="分类获取投注记录详细内容",
     *   description="请求参数<br>clas: 0__(双下划线)80__(双下划线)22(80 游戏类型id,22 live_id)<br>gn_clas: 0__(双下划线)53363(53363 游戏id)",
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
     *     name="clas",
     *     in="formData",
     *     description="",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="gn_clas",
     *     in="formData",
     *     description="",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="game_time",
     *     in="formData",
     *     description="固定为1",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="page",
     *     in="formData",
     *     description="页码",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="page_limit",
     *     in="formData",
     *     description="每页条数",
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
    case 'getrecorddetail':
        //###########################################################################
        $h_instance = new Harris_Sanitize();
        $_POST = $h_instance->sanitize($_POST);
        $rules = [
            'username' => 'required|alpha_numeric|max_len,12|min_len,3',
            'clas' => 'required',
            'gn_clas' => 'required',
            'game_time' => 'required|numeric',
            'page' => 'required|numeric',
            'page_limit' => 'required|numeric',
        ];
        $filters = [
            'username' => 'trim|sanitize_string',
            'clas' => 'trim',
            'gn_clas' => 'trim',
            'game_time' => 'trim',
            'page' => 'trim|intval',
            'page_limit' => 'trim|intval',
        ];
        $_POST = $h_instance->filter($_POST, $filters);
        $validated = $h_instance->validate($_POST, $rules);
        if ($validated !== true) {
            $declare_chinese = [
                'username' => '用户名',
                'clas' => 'clas',
                'gn_clas' => 'gn_clas',
                'game_time' => 'game_time',
                'page' => 'page',
                'Page Limit' => 'page_limit',
            ];
            $v_error = $h_instance->get_readable_errors();
            $v_error = $h_instance->h_ack_message($v_error);
            $v_error = $h_instance->h_ack_translate($declare_chinese, $v_error);
            $re = [
//                'code'=>$objCode->general_error->code,
                'code' => 10001,
                'errMsg' => $v_error,
            ];
            echo json_encode($re);
        } else {
            $class = $_POST['clas'];
            $class2 = $_POST['gn_clas'];
            $time = $_POST['game_time'];
            $username = $_POST['username'];
            $page = $_POST['page'];
            $pageLimit = $_POST['page_limit'];

            $cl = explode("__", $class);
            $siteid = SITE_ID;
            $liveid = str_replace(["+", "active", " "], ["", "", ""], $cl[2]);
            $gameKind = $cl[1];
            $cl2 = explode("__", $class2);
            $gameType = $cl2[1];
            if ($time == 1) {
                $betTimeBegin = date("Y-m-d");
                $betTimeEnd = date("Y-m-d");
            } else {
                $betTimeBegin = trim($gameType);
                $betTimeEnd = trim($gameType);
                $gameType = '';
            }
            $starTime = "00:00:00";
            $endTime = "23:59:59";
            $str = $siteid . $username . $liveid . $gameKind . $gameType . $betTimeBegin . $betTimeEnd . $starTime . $endTime . $page . $pageLimit;
            $key = "pdrft" . md5($str) . 'jhsefb';
            $params = [
                'siteId' => $siteid,
                'username' => $username,
                'liveId' => $liveid,
                'gameKind' => $gameKind,
                'gameType' => $gameType,
                'betTimeBegin' => $betTimeBegin,
                'betTimeEnd' => $betTimeEnd,
                'startTime' => $starTime,
                'endTime' => $endTime,
                'page' => $page,
                'pageLimit' => $pageLimit,
                'key' => $key
            ];
            if ($time == 2) {
                unset($params['gameType']);
            }
            $config = CommonClass::getSiteConfig($clientA);
            $clientB = new HproseSwooleClient(MY_TCP_CENTER_HOST); //小温
            $result = $clientB->listDetailReport(json_encode($params));
            //####################################################################
            $log_to_write = [
                'start' => "########################################################",
                'constant' => MY_TCP_CENTER_HOST,
                'goto' => "swoole listDetailReport \n ------------------------------------",
                'input' => json_encode($params, JSON_UNESCAPED_UNICODE),
                'result' => $result,
                'level' => $action
            ];
            $log_write = log_args_write($log_to_write);
            //#####################################################################
            $res = json_decode($result, TRUE);
            if ($res['returnCode'] == 900000) {
                $res['code'] = '1000';
                if (!empty($res['dataList'])) {
                    foreach ($res['dataList'] as $i => $list) {
                        if (isset($list['gameType'])) {
                            if ($list['gameType'] == 3001) {//波音牌型分解
                                $res['dataList'][$i]['carddetail'] = CommonClass::disassemble_poker_bbin($list['card']);
                                $zx = explode(",", $list['result']);
                                $res['dataList'][$i]['result'] = '庄' . $zx[0] . '点,闲' . $zx[1] . '点';
                            } else if ($list['gameType'] == 41001) {
                                $res['dataList'][$i]['liveMemberReportDetails'] = CommonClass::amount_bet($res['dataList'][$i]['liveMemberDetails']);
                                $res['dataList'][$i]['pokerList'] = CommonClass::disassemble_poker($res['dataList'][$i]['pokerListArr']);
                                $res['dataList'][$i]['bankResult'] = CommonClass::disassemble_type($res['dataList'][$i]['bankResultArr']);
                            }
                        }
                    }
                }
            } else {
                $res['code'] = '1001';
            }
            $res['params'] = $params;
            //$result = str_replace(array('"[', ']"', '\"', '\\\\', '"{', '}"'), array('[', ']', '"', '\\', '{', '}'), $result);
            echo json_encode($res);

        }
        //###########################################################################
        break;
    /**
     * @SWG\Post(
     *   path="/record.php?action=getrecordhistory",
     *   tags={"报表日志"},
     *   summary="分类获取投注历史记录",
     *   description="请求参数<br>：clas 0__(双下划线)80__(双下划线)22(80 游戏类型id,22 live_id)",
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
     *     name="clas",
     *     in="formData",
     *     description="",
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
    case 'getrecordhistory'://历史记录，按天统计
        $class = CommonClass::filter_input_init(INPUT_POST, 'clas', FILTER_SANITIZE_MAGIC_QUOTES);
        //$time = CommonClass::filter_input_init(INPUT_POST, 'game_time');
        $username = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        $cl = explode("__", $class);
        $siteid = SITE_ID;
        $liveid = str_replace([
            "+",
            "active",
            " "
        ], [
            "",
            "",
            ""
        ], $cl[2]);
        $gameKind = $cl[1];

        $betTimeBegin = date("Y-m-d", time() - 3600 * 24 * 8);
        $betTimeEnd = date("Y-m-d", time() - 3600 * 24);
        $starTime = "00:00:00";
        $endTime = "23:59:59";
        $str = $siteid . $username . $liveid . $gameKind . $betTimeBegin . $betTimeEnd . $starTime . $endTime;

        $key = "dsasf" . md5($str) . 'dserft';
        $params = [
            'siteId' => $siteid,
            'username' => $username,
            'liveId' => $liveid,
            'gameKind' => $gameKind,
            'betTimeBegin' => $betTimeBegin,
            'betTimeEnd' => $betTimeEnd,
            'startTime' => $starTime,
            'endTime' => $endTime,
            'key' => $key
        ];
        $config = CommonClass::getSiteConfig($clientA);
        $clientB = new HproseSwooleClient(MY_TCP_CENTER_HOST); //小温
        $result = $clientB->betTotalByDay(json_encode($params));
        /*        if($username == 'linux' || $username=='admin'){
          echo "<pre>";
          echo MY_TCP_CENTER_HOST.'<br/>';
          print_r($params);
          echo "</pre>";
          } */

        $result = str_replace([
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
        ], $result);
        $r = json_decode($result, TRUE);
        $r['post_arr'] = $params;
        if (isset($r['returnCode']) && $r['returnCode'] == '900000') {
            $r['code'] = '1000';
        } else {
            $r['code'] = '1001';
        }
        $result = json_encode($r);
        echo $result;
        break;
    /**
     * @SWG\Post(
     *   path="/record.php?action=getrecordtotal",
     *   tags={"报表日志"},
     *   summary="投注总记录",
     *   description="所有投注记录的总和
    <br>获取今日：{betTime: 2017-01-12(四), betCount: 0, betamount: 0, winlose(输赢): 0, validamount(有效下注): 0, water: 0}
    <br>获取一周历史：",
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
     *     name="istoday",
     *     in="formData",
     *     description="1：今天的 2：一周的历史",
     *     required=true,
     *     type="string",
     *   ),     *
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
    case 'getrecordtotal'://今天历史报表，按天统计
        $username = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        $siteid = SITE_ID;
        $istoday = CommonClass::filter_input_init(INPUT_POST, 'istoday', FILTER_SANITIZE_MAGIC_QUOTES);
        if ($istoday == 1) {//今日投注报表
            $betTimeBegin = date("Y-m-d");
            $betTimeEnd = date("Y-m-d");
        } else {//历史7天投注报表
            $betTimeBegin = date("Y-m-d", time() - 3600 * 24 * 7);
            $betTimeEnd = date("Y-m-d", time() - 3600 * 24);
        }
        $starTime = "00:00:00";
        $endTime = "23:59:59";
        $str = $siteid . $username . $betTimeBegin . $betTimeEnd . $starTime . $endTime;

        $key = "dsasf" . md5($str) . 'dserft';
        $params = [
            'siteId' => $siteid,
            'username' => $username,
            'betTimeBegin' => $betTimeBegin,
            'betTimeEnd' => $betTimeEnd,
            'startTime' => $starTime,
            'endTime' => $endTime,
            'key' => $key
        ];
        $config = CommonClass::getSiteConfig($clientA);
        $clientB = new HproseSwooleClient(MY_TCP_CENTER_HOST); //小温
        $result = $clientB->betTotalByDay(json_encode($params));
        $result = str_replace([
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
        ], $result);
        $result = $clientA->getUserReturnWater($result, SITE_ID, $username, $istoday);
        //$result =  json_decode($result,TRUE);
        //$result['params'] = $params;
        //echo json_encode($result);
        echo $result;
        break;
    /**
     * @SWG\Post(
     *   path="/record.php?action=getmoneycheck",
     *   tags={"存提款"},
     *   summary="出款稽核",
     *   description="",
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
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="103041",
     *     description="获取稽核成功"
     *   ),
     *   @SWG\Response(
     *     response="113042",
     *     description="获取稽核失败,请联系客服"
     *   )
     *  )
     */
    case 'getmoneycheck'://出款稽核 //TODO 登录验证 稽核保存在session
        if ($jsonp == 'jsonp') {  //后台远程调用
            $params['username'] = CommonClass::filter_input_init(INPUT_GET, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
            $params['site_id'] = CommonClass::filter_input_init(INPUT_GET, 'site_id', FILTER_SANITIZE_MAGIC_QUOTES);
        } else {
            $params['username'] = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
            $params['site_id'] = SITE_ID;
        }
        //##############################################################################
        $log_to_write = [
            'start' => "============稽核开始==========\r\n",
            '用户名' => $params['username'],
            'siteid' => $params['site_id'],
            '请求参数' => json_encode($params, JSON_UNESCAPED_UNICODE),
            'level' => 'jihe-' . $action
        ];
        $log_write = log_args_write($log_to_write);
        //##############################################################################
        $checklist = goto_center_api('getCheckList', $params);
        if ($checklist['code'] == $objCode->success_get_checklist->code) {
            $get_last_money_param = [
                'site_id' => SITE_ID,
                'username' => $params['username']
            ];
            $lastAllMoney = goto_center_api('getLastMoney', $get_last_money_param);//所有平台的余额
            //##############################################################################
            $log_to_write = [
                '余额统计' => $lastAllMoney = is_array($lastAllMoney) ? json_encode($lastAllMoney, JSON_UNESCAPED_UNICODE) : $lastAllMoney,
                '稽核统计' => json_encode($checklist, JSON_UNESCAPED_UNICODE),
                'level' => 'jihe-' . $action
            ];
            $log_write = log_args_write($log_to_write);
            //##############################################################################
            $allfree = 0; //稽核手续费
            $alldeleteporoms = 0; //需扣除优惠金额
            $allmoney = 0; //总投注量
            $list = json_decode($checklist['info']['list'], TRUE);
            if (!empty($list[0])) {

                $config = CommonClass::getSiteConfig($clientA);

                $yuecode = 0; //有余的打码量
                //$yh_yuecode = 0; //优惠有余的打码量
                $num = 0;
                foreach ($list as $i => $v) {//计算稽核是否通过
                    $str = $params['site_id'] . $v['username'] . date("Y-m-d", $v['start_time']) . date("Y-m-d", $v['end_time']) . date("H:i:s", $v['start_time']) . date("H:i:s", $v['end_time']);
                    $data = [
                        'siteId' => $params['site_id'],
                        'username' => $v['username'],
                        'betTimeBegin' => date("Y-m-d", $v['start_time']),
                        'betTimeEnd' => date("Y-m-d", $v['end_time']),
                        'startTime' => date("H:i:s", $v['start_time']),
                        'endTime' => date("H:i:s", $v['end_time']),
                        'key' => CommonClass::get_key_param($str, 5, 6)
                    ];
                    //$result1 = $clientB->auditTotal(json_encode($data));
//                    $clientB = new HproseSwooleClient(MY_TCP_CENTER_HOST); //小温
                    $num++;
//                    list($usec, $sec) = explode(" ", microtime());
//                    $t1 = ((float)$usec + (float)$sec);
                    $result1 = goto_coffee_mix('auditTotalTemp', MY_TCP_CENTER_HOST, json_encode($data)); //小温
//                    $t2 = ((float)$usec + (float)$sec);
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
                    if ($result['returnCode'] == '900000') {
                        $list[$i]['sum_money'] = isset($result['dataList']['totalValidamount']) ? $result['dataList']['totalValidamount'] : 0; //实际投注，总投注

                        $allmoney += $list[$i]['sum_money'];

                        $list[$i]['live_money'] = isset($result['dataList']['liveValidamount']) ? $result['dataList']['liveValidamount'] : 0; //实际投注，视讯投注
                        $list[$i]['sports_money'] = isset($result['dataList']['sportValidamount']) ? $result['dataList']['sportValidamount'] : 0; //实际投注，体育投注
                        $list[$i]['lotto_money'] = isset($result['dataList']['lottoValidamount']) ? $result['dataList']['lottoValidamount'] : 0; //实际投注，彩票投注
                        $list[$i]['game_money'] = isset($result['dataList']['jilvValidamount']) ? $result['dataList']['jilvValidamount'] : 0; //实际投注，几率投注

                        $list[$i]['normal_code'] = $v['normal_code'] = is_null($v['normal_code']) ? 0 : $v['normal_code']; //常态打码量
                        $list[$i]['promos_code'] = $v['promos_code'] = is_null($v['promos_code']) ? 0 : $v['promos_code']; //优惠打码量
                        $list[$i]['save_money'] = $v['save_money'] = is_null($v['save_money']) ? 0 : $v['save_money']; //存款金额
                        $list[$i]['promos_money'] = $v['promos_money'] = is_null($v['promos_money']) ? 0 : $v['promos_money']; //优惠金额
                        if ($list[$i]['promos_money'] == 0) {
                            $list[$i]['promos_code'] = 0;
                        }
                        $list[$i]['extend_code'] = $v['extend_code'] = is_null($v['extend_code']) ? 0 : $v['extend_code']; //放宽打码量

                        $list[$i]['p_is_ok'] = '<span style="color:green"> V </span>';
                        $list[$i]['p_is_m_money'] = '<span style="color:green"> 否 </span>';
                        $list[$i]['p_m_money'] = 0;

                        $list[$i]['m_is_ok'] = '<span style="color:green"> V </span>';
                        $list[$i]['m_is_m_money'] = '<span style="color:green"> 否 </span>';
                        $list[$i]['m_m_money'] = 0;

                        $yuecode += $list[$i]['sum_money'];
                        //常态稽核
                        if ($v['normal_code'] > 0) {
                            if ($yuecode - $v['normal_code'] + $v['extend_code'] >= 0) {
                                $yuecode = $yuecode - $v['normal_code'] + $v['extend_code']; //有余的打码量累计到下一条稽核
                                $list[$i]['normal_check_status'] = 1; //通过常态稽核，不需扣除手续费
                                $list[$i]['normal_free'] = 0; //通过常态稽核，不需扣除手续费
                            } else {
                                $list[$i]['normal_check_status'] = -1; //没通过常态稽核，需扣除手续费
                                $allfree += $list[$i]['normal_free']; //统计常态稽核手续费
                                //$yuecode += $list[$i]['sum_money']; //有余的打码量累计到下一条稽核

                                $list[$i]['m_is_ok'] = '<span style="color:red"> X </span>';
                                $list[$i]['m_is_m_money'] = '<span style="color:red"> ' . $list[$i]['normal_free'] . ' </span>';
                                $list[$i]['m_m_money'] = $list[$i]['normal_free'];
                            }
                        } else {//不需稽核，不需扣除手续费
                            $list[$i]['normal_free'] = 0;
                            $list[$i]['normal_check_status'] = 1;
                            //$yuecode += $list[$i]['sum_money']; //有余的打码量累计到下一条稽核
                        }
                        //优惠稽核
                        if ($list[$i]['promos_code'] > 0) {//综合打码量大于0
                            $is_acoss_normal = 0;
                            if ($v['normal_code'] > 0 && $list[$i]['normal_check_status'] == 1) {//通过常态加上常态打码
                                $is_acoss_normal = $v['normal_code'];
                            }

                            if ($yuecode + $is_acoss_normal - $v['promos_code'] >= 0) {//通过优惠稽核
                                $yuecode = $yuecode + $is_acoss_normal - $v['promos_code'];  //有余的打码量累计到下一条稽核
                                $list[$i]['poroms_check_status'] = 1; //通过优惠稽核，不需扣除优惠
                            } else {
                                if ($lastAllMoney < $list[$i]['promos_ye_check_limit'] && $lastAllMoney !== FALSE) {//余额优惠稽核不需消耗打码量
                                    //$yuecode = $yuecode - $v['promos_code'];  //有余的打码量累计到下一条稽核
                                    $list[$i]['poroms_check_status'] = 1; //通过优惠稽核，不需扣除优惠
                                } else {
                                    $alldeleteporoms += $list[$i]['promos_money']; //需扣除金额统计
                                    //$yuecode += $list[$i]['sum_money']; //有余的打码量累计到下一条稽核
                                    $list[$i]['poroms_check_status'] = -1;

                                    $list[$i]['p_is_ok'] = '<span style="color:red"> X </span>';
                                    $list[$i]['p_is_m_money'] = '<span style="color:red"> ' . $list[$i]['promos_money'] . ' </span>';
                                    $list[$i]['p_m_money'] = $list[$i]['promos_money'];
                                }
                            }
                        } else {
                            $list[$i]['poroms_check_status'] = 1; //通过优惠稽核，不需扣除优惠
                            //$yuecode += $list[$i]['sum_money']; //有余的打码量累计到下一条稽核
                        }
                    } else {
                        $re = [
                            'code' => $objCode->fail_get_checklist->code,
                            'error' => 'report error -_-!',
                            'result' => $result,
                            'request' => $data
                        ];
                        echo CommonClass::ajax_return($re, $jsonp, $jsonpcallback);
                        exit();
                    }
                    $list[$i]['start_time'] = date("y-m-d H:i:s", $list[$i]['start_time']);
                    $list[$i]['end_time'] = date("y-m-d H:i:s", $list[$i]['end_time']);
                }
                //##############################################################################
                $log_to_write = [
                    '稽核下注统计' => json_encode($list, JSON_UNESCAPED_UNICODE),
                    'level' => 'jihe-' . $action
                ];
                $log_write = log_args_write($log_to_write);
                //##############################################################################
            }
            $checklist['data']['list'] = $list;
            $checklist['data']['allfree'] = $allfree; //稽核手续费
            $checklist['data']['alldeleteporoms'] = $alldeleteporoms; //稽核扣除优惠
            $checklist['data']['allmoney'] = $allmoney; //总投注量
            $get_reply_out_free_params = [
                'site_id' => $params['site_id'],
                'username' => $params['username']
            ];
            $replyfree = goto_center_api('getReplyOutFree', $get_reply_out_free_params);
            //##############################################################################
            $log_to_write = [
                '稽核用户信息统计' => json_encode($replyfree, JSON_UNESCAPED_UNICODE),
                'END' => "\r\n==================================稽核结束========================\r\n",
                'level' => 'jihe-' . $action
            ];
            $log_write = log_args_write($log_to_write);
            //##############################################################################

            $checklist['data']['replyhours'] = $replyfree['reply_hours'];
            $checklist['data']['replytime'] = $replyfree['reply_time'];
            $checklist['data']['take_money_max'] = $replyfree['take_money_max'];
            $checklist['data']['take_money_min'] = $replyfree['take_money_min'];
            if ($replyfree['reply_fee']) {
                $checklist['data']['replyfree'] = $replyfree['reply_fee'];
            } else {
                $checklist['data']['replyfree'] = 0;
            }
            $checklist['data']['all_free_proms_reply'] = $allfree + $alldeleteporoms + $replyfree['reply_fee'];
            //组装稽核数据
            $str_to_log = '';
            if ($list) {
                foreach ($list as $key => $value) {
                    if ($value['start_time'] != '') {
                        $str_to_log .= $value['start_time'] . ',' . $value['end_time'] . ',' . $value['sum_money'] . ',' . $value['save_money'] . ',' . $value['promos_money'] . ';';
                        $str_to_log .= $value['normal_code'] . ',' . $value['extend_code'] . ',' . $value['m_is_ok'] . ',' . $value['m_is_m_money'] . ';';
                        $str_to_log .= $value['promos_code'] . ',' . $value['p_is_ok'] . ',' . $value['promos_money'] . '#';
                    }
                }
            }
            if ($checklist['data']['replyfree'] > 0) {
                if ($str_to_log != '') {
                    $str_to_log .= '*' . $replyfree['reply_hours'] . ',' . $replyfree['reply_time'] . ',' . $checklist['data']['replyfree'] . ';';
                } else {
                    $str_to_log = '没有稽核信息*' . $replyfree['reply_hours'] . ',' . $replyfree['reply_time'] . ',' . $checklist['data']['replyfree'] . ';';
                }
                $str_to_log .= $checklist['data']['all_free_proms_reply'];
            } else {
                if ($str_to_log != '') {
                    $str_to_log .= '*' . $replyfree['reply_hours'] . ',' . $replyfree['reply_time'] . ',' . '0' . ';';
                    $str_to_log .= $checklist['data']['all_free_proms_reply'];
                }
            }
            //$_SESSION['jihe'] = $str_to_log;
            //$checklist['data']['all_free_proms_reply'] = $allfree + $alldeleteporoms;
            if ($jsonp != 'jsonp') {  //后台远程调用
                $_SESSION['all_need_free'] = $checklist['data']['all_free_proms_reply']; //总需扣除金额
                $_SESSION['check_free'] = $checklist['data']['allfree']; //常态稽核费用
                $_SESSION['check_poroms_free'] = $checklist['data']['alldeleteporoms']; //优惠稽核需扣除金额
                $_SESSION['check_reply_free'] = $checklist['data']['replyfree']; //重复出款超次数需手续费
                $_SESSION['take_money_max'] = $checklist['data']['take_money_max']; //最大出款限额
                $_SESSION['take_money_min'] = $checklist['data']['take_money_min']; //最小出款限额
            }
            $checklist['data']['jihe'] = $str_to_log;
            $checklist['data']['check_time'] = date("Y-m-d H:i:s");
        }
        //存入redis
        $redis = CommonClass::redisconnect(REDIS_DB);
        $rk = CommonClass::get_rule_key($params['site_id'], $params['username']);
        $redis->set($rk, json_encode($checklist));
        $redis->expire($rk, 600);
        unset($checklist['info']);
        unset($checklist['data']['list'], $checklist['data']['jihe']);//nga_peit_htar_dar 占时把他去掉运维蛮牛里面没有展现给客户看
        echo CommonClass::ajax_return($checklist, $jsonp, $jsonpcallback);

        break;
    /**
     * @SWG\Post(
     *   path="/record.php?action=getmoneytake",
     *   tags={"存提款"},
     *   summary="获取出款信息(先调用出款稽核)",
     *   description="",
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
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="90000",
     *     description="获取用户出款资料成功"
     *   ),
     *   @SWG\Response(
     *     response="211026",
     *     description="获取用户出款资料失败"
     *   ),
     *   @SWG\Response(
     *     response="90001",
     *     description="获取用户最大出款限额失败"
     *   )
     *  )
     */
    case "getmoneytake":
//        if (isset($_SESSION['take_money_max'])) {
//            $data['need_free'] = $_SESSION['all_need_free']; //总需扣除金额
//            $data['normal_free'] = $_SESSION['check_free']; //常态稽核费用
//            $data['poroms_free'] = $_SESSION['check_poroms_free']; //优惠稽核需扣除金额
//            $data['reply_free'] = $_SESSION['check_reply_free']; //重复出款超次数需手续费
//            $data['money_max'] = $_SESSION['take_money_max']; //最大出款限额
//            $data['money_min'] = $_SESSION['take_money_min']; //最小出款限额

        $params['username'] = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['site_id'] = SITE_ID;

        $redis = CommonClass::redisconnect(REDIS_DB);
        $rk = CommonClass::get_rule_key($params['site_id'], $params['username']);

        $rule = $redis->get($rk);
        $limit_temp = json_decode($rule, TRUE);
        $limit = $limit_temp['data'];
        //echo $rk;
        //print_r($limit);

        $data['need_free'] = $limit['all_free_proms_reply']; //总需扣除金额
        $data['normal_free'] = $limit['allfree']; //常态稽核费用
        $data['poroms_free'] = $limit['alldeleteporoms']; //优惠稽核需扣除金额
        $data['reply_free'] = $limit['replyfree']; //重复出款超次数需手续费
        $data['money_max'] = $limit['take_money_max']; //最大出款限额
        $data['money_min'] = $limit['take_money_min']; //最小出款限额
        if (isset($limit['take_money_max'])) {
            $datar = $clientA->getGetMoneyUserDetail($params);
            if ($datar['code'] == $objCode->success_get_getmoney_userdetial->code) {
                $info = json_decode($datar['info'], TRUE);
                $address = $info['bank_name'] . " -- " . $info['bank_adress'] . " -- " . $info['bank_account'] . " -- " . $info['realname'];
                $data['address'] = $address;
                $limit_temp['data']['address'] = $address;
                //增加存款信息
                $redis->set($rk, json_encode($limit_temp));
                $return = ['data' => $data, 'code' => 90000];
            } else {
                $return = ['code' => $datar['code']];
            }
        } else {
            $return = ['code' => 90001];
        }
        echo CommonClass::ajax_return($return, $jsonp, $jsonpcallback);
        break;
    /**
     * @SWG\Post(
     *   path="/record.php?action=takesubmit",
     *   tags={"存提款"},
     *   summary="提交出款",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="money",
     *     in="formData",
     *     description="出款金额",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="gold",
     *     in="formData",
     *     description="实际出款金额",
     *     required=true,
     *     type="string"
     *   ),
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
     *     name="agent",
     *     in="formData",
     *     description="代理",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="get_password",
     *     in="formData",
     *     description="出款密码",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="account",
     *     in="formData",
     *     description="银行信息（杭州银行 -- 上海 -- 1231231231221 -- qwerwq）",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="103047",
     *     description="添加出款订单成功"
     *   ),
     *   @SWG\Response(
     *     response="113048",
     *     description="添加出款订单失败"
     *   ),
     *   @SWG\Response(
     *     response="113051",
     *     description="出款密码错误"
     *   ),
     *   @SWG\Response(
     *     response="113056",
     *     description="您存款操作已被停用，请联系客服"
     *   )
     *  )
     */
    case "takesubmit":
        $get_input = ['money', 'gold', 'username', 'oid', 'get_password'];
        $neglect = ['gold'];
        $re = check_param($get_input, $params, $neglect);
        if ($re !== true) {
            break;
        }
        $params['out_money'] = $params['money'];
        $params['real_out_money'] = $params['gold'];
        $get_password = $params['get_password'];

        $redis = CommonClass::redisconnect(REDIS_DB);
        $rk = CommonClass::get_rule_key($params['site_id'], $params['username']);
        $rule = $redis->get($rk);
        $limit_temp = json_decode($rule, TRUE);
        $limit = $limit_temp['data'];

        if (isset($limit['take_money_max']) && isset($limit['jihe']) && isset($limit['address'])) {
            $need_free = $limit['all_free_proms_reply']; //总需扣除金额
            $normal_free = $limit['allfree']; //常态稽核费用
            $poroms_free = $limit['alldeleteporoms']; //优惠稽核需扣除金额
            $reply_free = $limit['replyfree']; //重复出款超次数需手续费
            $money_max = $limit['take_money_max']; //最大出款限额
            $money_min = $limit['take_money_min']; //最小出款限额

            $logstrs = $limit['jihe']; //获取稽核数据
            $account = $limit['address']; //账户
            $accounts = explode('--', $account);
            //直接读取用户信息,不通过接口输入

            $params['promos_money'] = $poroms_free;
            $params['fee_money'] = $normal_free;
            $params['reply_free_money'] = $reply_free;
            if (empty($get_password)) {//不符合条件的金额，非法提交的
                $log_to_write = [
                    'input' => json_encode($params, JSON_UNESCAPED_UNICODE),
                    'redis 数据 rule' => $rule,
                    '原因' => 'error 0 密码不能为空',
                    'level' => $action
                ];
                $log_write = log_args_write($log_to_write);
                $return = [
                    'code' => $objCode->fail_take_money_order->code,
                    'error' => 'error 0',
                    'tips' => $params,
                    'nf' => $need_free,
                    'errMsg' => '密码不能为空'
                ];
                session_destroy(); //清掉session
                echo CommonClass::ajax_return($return, $jsonp, $jsonpcallback);
                break;
            }
            if (empty($params['out_money']) || !CommonClass::check_money($params['out_money'])) {//不符合条件的金额，非法提交的
                $log_to_write = [
                    'input' => json_encode($params, JSON_UNESCAPED_UNICODE),
                    'redis 数据 rule' => $rule,
                    '原因' => 'error 0 请输入正确的出款金额',
                    'level' => $action
                ];
                $log_write = log_args_write($log_to_write);
                $return = [
                    'code' => $objCode->fail_take_money_order->code,
                    'error' => 'error 0',
                    'tips' => $params,
                    'nf' => $need_free,
                    'errMsg' => '请输入正确的出款金额'
                ];
                session_destroy(); //清掉session
                echo CommonClass::ajax_return($return, $jsonp, $jsonpcallback);
                break;
            }
            if ($params['out_money'] > $money_max || $params['out_money'] < $money_min) {//不符合条件的金额，非法提交的
                $log_to_write = [
                    'input' => json_encode($params, JSON_UNESCAPED_UNICODE),
                    'redis 数据 rule' => $rule,
                    '原因' => 'error 0 出款金额不在限额范围内',
                    'level' => $action
                ];
                $log_write = log_args_write($log_to_write);
                $return = [
                    'code' => $objCode->fail_take_money_order->code,
                    'error' => 'error 0',
                    'tips' => $params,
                    'nf' => $need_free,
                    'errMsg' => '出款金额不在限额范围内'
                ];
                session_destroy(); //清掉session
                echo CommonClass::ajax_return($return, $jsonp, $jsonpcallback);
                break;
            }
            $get_password_right_param = [
                'company' => SITE_ID,
                'username' => $params['username'],
                'get_password' => $get_password
            ];
            $isRight = goto_center_api('isGetPasswordRight', $get_password_right_param);//判断出款密码是否正确
            if ($isRight != 1) {//出款密码错误
                $log_to_write = [
                    'redis 数据 rule' => $rule,
                    'isRight' => $isRight,
                    '原因' => '出款密码错误',
                    'level' => $action
                ];
                $log_write = log_args_write($log_to_write);
                echo CommonClass::ajax_return([
                    'code' => $objCode->error_getpassword->code,
                    'pwd' => $get_password,
                    'errMsg' => '出款密码错误'
                ], $jsonp, $jsonpcallback);
                break;
            }
            //#######################################################################
            // 判断提款权限
            $kilo['company'] = SITE_ID;
            $kilo['username'] = $params['username'];
            $session = $clientA->getSession($kilo);
            if ($session['code'] == 201018) {
                $res = json_decode($session['info']);
                $money_status = $res->money_status;
                if ($money_status != 1) {
                    echo CommonClass::ajax_return([
                        'code' => $objCode->is_not_allow_save->code,
                        'errMsg' => '您的存/取款操作已被停用，请联系客服'
                    ], $jsonp, $jsonpcallback);
                    break;
                }
            }
            //#######################################################################
            $log_to_write = [
                'session' => $session,
                '参数' => json_encode($params, JSON_UNESCAPED_UNICODE),
                'going_to' => 'isAlowSaveTake',
                'level' => $action
            ];
            $log_write = log_args_write($log_to_write);
            $is_allow_save_param = [
                'site_id' => SITE_ID,
                'username' => $params['username']
            ];
            $isAlow = goto_center_api('isAlowSaveTake', $is_allow_save_param);//判断允许存取款
            if ($isAlow != 1) {//不允许存取款
                $log_to_write = [
                    'is_allow' => $isAlow,
                    'level' => $action
                ];
                $log_write = log_args_write($log_to_write);
                echo CommonClass::ajax_return([
                    'code' => $objCode->is_not_allow_save->code,
                    'errMsg' => '您的存/取款操作已被停用，请联系客服'
                ], $jsonp, $jsonpcallback);
                break;
            }
            //实际出款金额,入库的时候会使用
            $params['real_out_money'] = $params['out_money'] - $need_free;

            $params['bank_name'] = trim($accounts[0]);
            $params['province'] = trim($accounts[1]);
            $params['bank_num'] = trim($accounts[2]);
            $params['bank_user'] = trim($accounts[3]);
            $params['add_ip'] = $ip;

            $config = CommonClass::getSiteConfig($clientA);
            $billno = CommonClass::get_billno(SITE_ID, $params['username'], $params['out_money']);
            $key = CommonClass::get_key_param(SITE_WEB . $params['username'] . $billno, 5, 6);
            $paramsb = [
                'fromKey' => SITE_WEB,
                'siteId' => SITE_ID,
                'username' => $params['username'],
                'remitno' => $billno,
                'remit' => $params['out_money'],
                'transType' => 'out',
                'fromKeyType' => '10009',
                'key' => $key,
                'memo' => '会员申请出款,单号：' . $billno
            ];

            $params['billno'] = $billno;
            $params['logstr'] = $logstrs;
            $log_to_write = [
                'params' => json_encode($params, JSON_UNESCAPED_UNICODE),
                'paramsb' => json_encode($paramsb, JSON_UNESCAPED_UNICODE),
                'going_to' => 'addTakeMoneyOrder',
                'level' => $action
            ];
            $log_write = log_args_write($log_to_write);
            unset($params['money'], $params['gold'], $params['get_password']);
            $addTakeMoneyOrder_arr = [
                'params' => $params,
                'paramsb' => $paramsb
            ];
            $return = goto_center_api('addTakeMoneyOrder', $addTakeMoneyOrder_arr);//添加出款订单~~~~~
            $log_to_write = [
                'after' => 'addTakeMoneyOrder',
                'return' => json_encode($return, JSON_UNESCAPED_UNICODE),
                'level' => $action
            ];
            $log_write = log_args_write($log_to_write);
//            if ($return['code'] == $objCode->success_take_money_order->code) {
//                $return['code'] ='10000';
//                $return['data'] = json_decode($return['info'], TRUE);
//                $return['message'] = $objCode->success_take_money_order->message;
//                unset($return['info']);
//            } else {
            if ($return['code'] != $objCode->success_take_money_order->code) {
                $return = [
                    'code' => $objCode->fail_take_money_order->code,
                    'error' => 'error 1',
                    'errMsg' => '添加出款订单错误'
                ];
            }
            //调用出款接口
//            $clientC = new HproseSwooleClient(MY_TCP_MONEY_HOST); //光光
//            $res = $clientC->transMoney(json_encode($paramsb));
//            $result = json_decode($res, TRUE);
//            if ($result['code'] == '100000') {//出款扣金额成功
//                $params['billno'] = $billno;
//                $logstrs = CommonClass::filter_input_init(INPUT_POST, 'str_to_log');
////                $x = explode(';', $logstrs);
////                if(is_array($x)){
////                    foreach($x as $i=>$v){
////                        $params['logstr'.$i] = $v;
////                    }
////                }
//                $params['logstr'] = $logstrs;
//                $return = $clientA->addTakeMoneyOrder($params); //添加出款订单~~~~~
//                if ($return['code'] == $objCode->success_take_money_order->code) {
//                    $return['data'] = json_decode($return['info'], TRUE);
//                    unset($return['info']);
//                } else {
//                    $return = array('code' => $objCode->fail_take_money_order->code, 'error' => 'error 1');
//                }
//            } else {//扣除金额失败
//                $return = array('code' => $objCode->fail_take_money_order->code, 'error' => 'error 2');
//            }
        } else {
            $log_to_write = [
                'input' => json_encode($params, JSON_UNESCAPED_UNICODE),
                'redis 数据 rule' => $rule,
                '原因' => 'error 3 没有稽核信息',
                'level' => $action
            ];
            $log_write = log_args_write($log_to_write);
            $return = [
                'code' => $objCode->fail_take_money_order->code,
                'error' => 'error 3',
                'errMsg' => '没有稽核信息'
            ];
        }
        echo CommonClass::ajax_return($return, $jsonp, $jsonpcallback);
        break;
    default:


        break;
}
