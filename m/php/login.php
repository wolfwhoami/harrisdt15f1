<?php
//ini_set("display_errors", "on");
header("Content-Type: text/html; charset=utf-8");
//error_reporting(0);
require_once("config.php");
require_once("Fetch.class.php");
require_once("Short_hand.php");//common used class by common function
require_once("api_common.php");//common function used in api
//########################################################################
$h_instance = new Harris_Sanitize();
$_REQUEST = $h_instance->sanitize($_REQUEST);
$rules = [
    'username' => 'required|alpha_numeric|max_len,12|min_len,3',
    'oid' => 'required',
    'lottoType' => 'alpha_numeric|max_len,3', //PC | MP;表示电脑|手机(ds需要)
];
$filters = [
    'username' => 'trim',
    'oid' => 'trim',
    'lottoType' => 'trim'
];
$_REQUEST = $h_instance->filter($_REQUEST, $filters);
$validated = $h_instance->validate($_REQUEST, $rules);
if ($validated !== true) {
    $declare_chinese = [
        'username' => '用户名',
        'oid' => '用户唯一识别码',
    ];
    $v_error = $h_instance->get_readable_errors();
    $v_error = $h_instance->h_ack_message($v_error);
    $v_error = $h_instance->h_ack_translate($declare_chinese, $v_error);
    $re['errMsg'] = $v_error;
    echo CommonClass::ajax_return($re, $jsonp, $jsonpcallback); // should be common use
    exit();
} else {
    $params = $_REQUEST;
    $params['company'] = SITE_ID;
    $re = goto_center_api('isLoginUpload', $params);
    //####################################################################
    $log_to_write = [
        'start' => " \n ########################################################",
        'goto' => "getBonusList \n ------------------------------------",
        'input' => json_encode($params, JSON_UNESCAPED_UNICODE),
        'result' => json_encode($re, JSON_UNESCAPED_UNICODE),
        'end' => "########################################################",
        'level' => 'login.php'
    ];
    $log_write = log_args_write($log_to_write);
    //#####################################################################
    $config = CommonClass::getSiteConfig($clientA);
    if ($re['code'] != $objCode->is_login_status->code) {//用户已经登出，或异常
        if ($action == 'ds_lottery' && $action == 'h8' && $action == 'ds_lotto' && $action == 'fenfen') {//内嵌
            echo 'logout';
        } else if ($action == 'store') {
            if (empty(STOREURL)) {
                print_r('地址为空');
                exit;
            }
            echo "<script>location.href='" . STOREURL . "';</script>";
        } else {
            echo "<script>alert('您还没登录，请先登录！'); self.close();</script>";
        }
        return false;
    }
    $session = json_decode($re['info'], TRUE);
    if (substr($session['user_type'], -1) == 1) {//主副站修改
        $PINGTAI_URL = NEW_BALANCE_URL_TEST;//新转账接口试玩地址
        $store_state = TRUE;//商城试玩
//    $AG_PREFIX = AG_PREFIX_TEST;
//    $BBIN_PREFIX = BBIN_PREFIX_TEST;
//    $DS_PREFIX = DS_PREFIX_TEST;
//    $H8_PREFIX = H8_PREFIX_TEST;
//
//    $AG_HASHCODE = AG_HASHCODE_TEST;
//    $BBIN_HASHCODE = BBIN_HASHCODE_TEST;
//    $DS_HASHCODE = DS_HASHCODE_TEST;
//    $H8_HASHCODE = H8_HASHCODE_TEST;
    } else {
        $PINGTAI_URL = NEW_BALANCE_URL;//新转账接口正式地址;
        $store_state = FALSE;//商城正式
//    $AG_PREFIX = AG_PREFIX;
//    $BBIN_PREFIX = BBIN_PREFIX;
//    $DS_PREFIX = DS_PREFIX;
//    $H8_PREFIX = H8_PREFIX;
//
//    $AG_HASHCODE = AG_HASHCODE;
//    $BBIN_HASHCODE = BBIN_HASHCODE;
//    $DS_HASHCODE = DS_HASHCODE;
//    $H8_HASHCODE = H8_HASHCODE;
    }
    /*未读取配置文件$PINGTAI_URL,$AG_LIVE_TYPE,$lang*/
    $AG_LIVE_TYPE = '2';
    $MG_LIVE_TYPE = '15';
    $BB_LIVE_TYPE = '11';
    $DS_LIVE_TYPE = '12';
    $LOOTO_LIVE_TYPE = '21';
    $H8_LIVE_TYPE = '13';
    $OG_LIVE_TYPE = '3';
//$og_password='20IuNngjW675ZMfamwnsr';
    $KEYB = NEW_KEYB;
    $f = new Fetch($PINGTAI_URL);
//$iswap = CommonClass::is_wap();
    $playType = 'MP';
//if($iswap){
//    $playType = 'PM';
//}
}
//########################################################################
$alert_message = [
    'ag' => 'AG贵宾',
    'bb' => 'BBIN旗舰',
    'bbgame' => 'BBIN电子游戏',
    'bbsport' => 'BBIN体育',
    'ds' => 'DS现场',
    'ds_lottery' => 'DS彩票',
    'ds_lotto' => '香港彩',
    'fenfen' => '分分彩',
    'h8' => 'h8', //10090
    'lotto' => 'KG经典彩',
    'mggame' => 'MG电子游戏',
];

$game_params = ['gameType', 'page_site', 'gamekind', 'lottoType', 'lottoTray', 'accType', 'line', 'action', 'bankingUrl', 'lobbyUrl', 'logoutRedirectUrl'];
foreach ($game_params as $value) {
    $game_getter_default[$value] = ''; //default game setting for merge array depends on game_params
}
switch ($action) {
    /**
     * @SWG\Post(
     *   path="/login.php?action=ag",
     *   tags={"游戏登录(等待外网环境)"},
     *   summary="ag登录游戏",
     *   description="返回结果<br>data:为失败原因",
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
     *     response="211014",
     *     description="未登录！"
     *   )
     * )
     */
    case 'ag':
        $plat = CommonClass::filter_input_init(INPUT_GET, 'plat', FILTER_SANITIZE_MAGIC_QUOTES);
        $gametype = CommonClass::filter_input_init(INPUT_GET, 'game_type', FILTER_SANITIZE_MAGIC_QUOTES);
        $plat = empty($plat) ? 'ag_live' : $plat;
        $action_type = $plat;
        $game_getter = [
            'gameType' => $gametype,
        ];
        common_game($action, $action_type, $game_getter);
        break;
    /**
     * @SWG\Post(
     *   path="/login.php?action=mg",
     *   tags={"游戏登录(等待外网环境)"},
     *   summary="mg登录游戏",
     *   description="返回结果<br>data:为失败原因",
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
     *     response="211014",
     *     description="未登录！"
     *   )
     * )
     */
    case 'mggame':
        $gameId = CommonClass::filter_input_init(INPUT_GET, 'id', FILTER_SANITIZE_MAGIC_QUOTES);
        $game_getter = [
            'gameType' => $gameId, //MG电子游戏
        ];
        common_game($action, 'mg_game', $game_getter);
        break;
    case 'bb':
        $game_getter = [
            'page_site' => 'live',
        ];
        common_game($action, 'bb_live', $game_getter);
        break;
    case 'bbsport':
        $game_getter = [
            'page_site' => 'ball',
        ];
        common_game($action, 'bb_sport', $game_getter);
        break;
    case 'bbgame':
        // $loginType = CommonClass::filter_input_init(INPUT_GET, 'lt', FILTER_SANITIZE_MAGIC_QUOTES);
        $gameType = CommonClass::filter_input_init(INPUT_GET, 'id', FILTER_SANITIZE_MAGIC_QUOTES);
        $game_getter = [
            'gameType' => $gameType,
            'page_site' => 'game',
            'gamekind' => 5,
        ];
        common_game($action, 'bb_game', $game_getter);
        break;
    case 'ds':
        $game_getter = [
            'gameType' => 'DS',
            'lottoType' => $playType,
        ];
        common_game($action, 'ds_live', $game_getter);
//        if ($re['status'] == 10000) {
//            echo "<script>location.href='{$re['message']['params']['link']}';</script>";
//            //header("location: {$re['message']['params']['link']}");
//        }
//        else if ($re['status'] == 10090) {//维护中
//            echo "<script>alert('DS平台维护中！'); self.close();</script>";
//            return false;
//        } else {//登录错误
//            echo "<script>alert('登录失败，请稍后再试！'); self.close();</script>";
//            return false;
//        }
        break;
    case 'dsgame':
        echo CommonClass::getLoadingPage('ds');
        $re = goto_center_with_paras('getProtectStatus', SITE_ID, 'ds_game');
        if ($re['code'] == $objCode->success_get_weihu->code) {
            $protect = json_decode($re['info'], TRUE);
            if (isset($protect['status'])) {
                if ($protect['status'] == 2) {
                    echo "<script>alert('DS电子游戏维护中，请玩其他游戏！'); self.close();</script>";
                    return false;
                }
            }
        }
        $id = CommonClass::filter_input_init(INPUT_GET, 'id', FILTER_SANITIZE_MAGIC_QUOTES);
        $oid = goto_center_with_paras('getSessionKeyforDsGame', SITE_ID, $params['username']);
        if (!is_array($oid)) {
            $url = DS_GAME_HOST . "&gameId={$id}&sessionKey={$oid}&language=zh";
            if ($session['user_type'] == 11 || $session['user_type'] == 21) {//主副站修改
                $url = DS_GAME_HOST_TEST . "&gameId={$id}&sessionKey={$oid}&language=zh";
            }
            echo "<script>location.href='{$url}';</script>";
        } else {
            echo CommonClass::ajax_return($oid, $jsonp, $jsonpcallback);
            exit();
        }
        break;
    case 'h8':
        $game_getter = [
            'lottoType' => $params['lottoType'],
            'accType' => 'HK',
            'action' => 'login',
        ];
        common_game($action, 'hb_sport', $game_getter);
        break;
    case 'lotto'://小鱼
        $dcUserTree = "{$session['super']}_{$session['corprator']},{$session['world']},{$session['agent']}";
        if (SITE_TYPE == 1) {//主站
            $DS_JD_HANDICP = PK_ZHU_JD_STATUS;
        } else {//副站
            $DS_JD_HANDICP = PK_FU_JD_STATUS;
        }
        $game_getter = [
            'gameType' => 'xiaoyu', //经典彩
            'lottoType' => $playType,
            'lottoTray' => $DS_JD_HANDICP,
            'accType' => $dcUserTree,
        ];
        common_game($action, 'ds_lotto_jd', $game_getter);
        break;
    case 'ds_lottery':
        if (SITE_TYPE == 1) {//主站
            $DS_SS_HANDICP = PK_ZHU_SS_STATUS;
        } else {//副站
            $DS_SS_HANDICP = PK_FU_SS_STATUS;
        }
        if (empty($DS_SS_HANDICP)) {
            echo '盘口为空';
            exit;
        }
        $game_getter = [
            'gameType' => 'LOTTERY',
            'lottoType' => $playType,
            'lottoTray' => $DS_SS_HANDICP,
        ];
        common_game($action, $action, $game_getter);
//        echo "<pre>";
//        print_r($r);
//        echo "<br />";
//        print_r($p);
//        echo "</pre>";
//        if(isset($re['status'])){
//            if ($re['status'] == 10000) {
//                echo $re['message']['params']['link'];
//            } else if ($re['status'] == 10090) {//维护中
//                echo 10090;
//                return false;
//            } else {//登录错误
//                echo 10090;
//                return false;
//            }
//        }else{
//            echo 10090;
//        }
        break;
    case 'ds_lotto':
        if (SITE_TYPE == 1) {//主站
            $DS_XG_HANDICP = PK_ZHU_XG_STATUS;
        } else {//副站
            $DS_XG_HANDICP = PK_FU_XG_STATUS;
        }
        $game_getter = [
            'gameType' => 'LOTTO',
            'lottoType' => $playType,
            'lottoTray' => $DS_XG_HANDICP,
        ];
        common_game($action, $action, $game_getter);
//        if(isset($re['status'])){
//            if ($re['status'] == 10000) {
//                echo $re['message']['params']['link'];
//            } else if ($re['status'] == 10090) {//维护中
//                echo 10090;
//                return false;
//            } else {//登录错误
//                echo 10090;
//                return false;
//            }
//        }else{
//            echo 10090;
//        }
        break;
    case 'fenfen':
        if (SITE_TYPE == 1) {//主站
            $DS_FF_HANDICP = PK_ZHU_FF_STATUS;
        } else {//副站
            $DS_FF_HANDICP = PK_FU_FF_STATUS;
        }
        //用户的级别树。方便分等级统计数据。 agent,world,corprator,super
        $dcUserTree = "{$session['super']},{$session['corprator']},{$session['world']},{$session['agent']}";
        $game_getter = [
            'gameType' => 'fenfen',
            'page_site' => 'cqssc',
            'lottoType' => $playType,
            'lottoTray' => $DS_FF_HANDICP,
            'accType' => $dcUserTree,
            'line' => 'companyds',
            'action' => 'bp3uqzx1v9mo4exu0cohvdtwmei2yrvz',
        ];
        common_game($action, 'ds_fenfen', $game_getter);
        break;
    case 'og':
        $p = [
            'gameType' => '1',
            'username' => $params['username'],
            'live' => $OG_LIVE_TYPE,
            'siteId' => SITE_ID,
            'key' => CommonClass::get_key_param($params['username'] . $KEYB . date("Ymd"), 4, 1)
        ];
//        $f->debug=TRUE;
        $p = http_build_query($p);
        $r = $f->NewLogin($p);
        if (strstr($r, 'status') === FALSE || strstr($r, 'message') === FALSE) {
            print_r($r);
            exit;
        }//判断是否连通
        $re = json_decode($r, TRUE);
        if ($re['status'] == 10000) {
            echo "<script>location.href='{$re['message']}';</script>";
        } else {
            print_r($re);
        }
        break;
    case 'store':
        $url = STOREURL;
        if (empty($url)) {
            print_r('地址为空');
            exit;
        }
        if ($store_state) {
            echo "<script>location.href='" . $url . "';</script>";
            return FALSE;
        }
        $re = goto_center_api('getUserPoint', $params);
        if ($re['code'] == $objCode->success_get_user_point->code) {
            require('encrypt/cls_encrypt.php');
            $key = '5fd5c12dc2d335c8522ceb9a52f1f8f1';
            $expire_time = time() + 1800;
            $data = json_decode($re['info'], TRUE);
            $str = $params['company'] . "\t" . $data['username'] . "\t" . $data['u_points'] . "\t" . $expire_time;
            $auth = XXTea::encrypt($str, $key);
            $token = rawurlencode($auth);
            echo "<script>location.href='{$url}/user.php?act=act_login&token={$token}';</script>";
        } elseif ($re['code'] == $objCode->fail_get_user_point->code) {
            echo '请稍后再试！！！';
        } elseif ($re['code'] == $objCode->is_not_login_status->code) {
            echo '用户已经登出';
        }
        break;
}
function common_game($action, $action_type, $game_getter)
{
    $original_action = $action;
    global $objCode, $alert_message, $params, $KEYB, $game_params, $f, $game_getter_default;
    $game_getter = array_merge($game_getter_default, $game_getter);
    extract($game_getter);
    $post_active ='';
    switch ($original_action) {
        case 'bb':
        case 'bbsport':
            global $BB_LIVE_TYPE;
            $get_load_page = 'bbin';
            $live = $BB_LIVE_TYPE;
            break;
        case 'bbgame':
            global $BB_LIVE_TYPE;
            $get_load_page = '';//bbin
            $live = $BB_LIVE_TYPE;
            break;
        case 'ds_lottery':
        case 'ds_lotto':
        case 'fenfen':
            global $DS_LIVE_TYPE;
            $get_load_page = '';//ds_lottery //ds_lotto //fenfen
            $live = $DS_LIVE_TYPE;
            break;
        case 'h8':
            global $H8_LIVE_TYPE;
            $get_load_page = '';//h8
            $live = $H8_LIVE_TYPE;
            break;
        case 'lotto':
            global $LOOTO_LIVE_TYPE;
            $get_load_page = '';//经典菜
            $live = $LOOTO_LIVE_TYPE;
            $post_active =1;
            break;
        case 'mggame':
            global $MG_LIVE_TYPE;
            $get_load_page = '';//mggame
            $live = $MG_LIVE_TYPE;
            break;
        default:  //ag,ds,
            global ${strtoupper($original_action) . '_LIVE_TYPE'};
            $get_load_page = $original_action;
            $live = ${strtoupper($original_action) . '_LIVE_TYPE'};
            break;
    }
    if (!empty($get_load_page)) {
        echo CommonClass::getLoadingPage($get_load_page);
    }
    $re = goto_center_with_paras('getProtectStatus', SITE_ID, $action_type);
    //####################################################################
    $log_to_write = [
        'start' => "\n ########################################################",
        'goto' => "getProtectStatus \n ------------------------------------",
        'param' => SITE_ID . "\n " . $action_type,
        'result' => json_encode($re, JSON_UNESCAPED_UNICODE),
        'end' => "########################################################",
        'level' => $original_action
    ];
    $log_write = log_args_write($log_to_write);
    //#####################################################################
    if ($re['code'] == $objCode->success_get_weihu->code) {
        $protect = json_decode($re['info'], TRUE);
        if (isset($protect['status'])) {
            if ($protect['status'] == 2) {
                echo "<script>alert('" . $alert_message[$original_action] . "厅维护中，请玩其他游戏！'); self.close();</script>";
                return false;
            }
        }
        $p = [
            'username' => $params['username'],
            'key' => CommonClass::get_key_param($params['username'] . $KEYB . date("Ymd"), 4, 1),
            'siteId' => SITE_ID,
            'live' => $live  //$AG_LIVE_TYPE   $BB_LIVE_TYPE
        ];
        foreach ($game_params as $key => $value) {
            if (!empty($$value)) {
                $p[$value] = $$value; //gameType='',$page_site='',$gamekind
            }
        }
        if ($original_action == 'lotto') //经典菜专用
        {
            $p['xiaoyuSiteId'] = SITE_ID;
            $p['loginChannel'] = 'login_jingdian';
        }
        $p = http_build_query($p);
        $r = $f->NewLogin($p,$post_active);
        //####################################################################
        $log_to_write = [
            'start' => "\n ########################################################",
            'goto' => "$p \n ------------------------------------",
            'url'=>$post_active,
            'result' => $r,
            'end' => "########################################################",
            'level' => $original_action
        ];
        $log_write = log_args_write($log_to_write);
        //#####################################################################
        if (strstr($r, 'status') === FALSE || strstr($r, 'message') === FALSE) {
            print_r($r);
            exit;
        }//判断是否连通
        $re = json_decode($r, TRUE);
        if ($re['status'] == 10000) {
            //echo $re['message'];//ag,bb,h8
            echo "<script>location.href='{$re['message']}';</script>"; //ag //bbgame,bbsport,lotto
        } elseif ($re['status'] == 100020) { //bbgame 电子邮箱
            echo CommonClass::bbwait();
        } else if ($re['status'] == 100030) {//bb
            echo "<script>alert('" . $alert_message[$original_action] . "厅维护中，请玩其他游戏！'); self.close();</script>";
            return false;
        } else {
            print_r($re);
        }
    }
}

?>
