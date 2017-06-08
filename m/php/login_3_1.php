<?php
ini_set("display_errors", "on");
header ( "Content-Type: text/html; charset=utf-8" );
//error_reporting(0);
require_once("config.php");
require_once("Fetch.class.php");
$params['username'] = CommonClass::filter_input_init(INPUT_GET, 'username');
$params['company'] = SITE_ID;
$params['oid'] = CommonClass::filter_input_init(INPUT_GET, 'oid');
$re = $clientA->isLoginUpload($params);
if (empty($params['oid']) || empty($params['username']) || empty($params['company']) || $re['code'] != $objCode->is_login_status->code) {//用户已经登出，或异常
    if($action == 'ds_lottery' && $action == 'h8' && $action == 'ds_lotto' && $action == 'fenfen'){//内嵌
        echo 'logout';
    }else{
        echo "<script>alert('您还没登录，请先登录！'); self.close();</script>";
    }
    return false;
}
$session = json_decode($re['info'], TRUE);
if (substr($session['user_type'], -1) == 1) {//主副站修改
    $AG_PREFIX = AG_PREFIX_TEST;
    $BBIN_PREFIX = BBIN_PREFIX_TEST;
    $DS_PREFIX = DS_PREFIX_TEST;
    $H8_PREFIX = H8_PREFIX_TEST;

    $AG_HASHCODE = AG_HASHCODE_TEST;
    $BBIN_HASHCODE = BBIN_HASHCODE_TEST;
    $DS_HASHCODE = DS_HASHCODE_TEST;
    $H8_HASHCODE = H8_HASHCODE_TEST;
} else {
    $AG_PREFIX = AG_PREFIX;
    $BBIN_PREFIX = BBIN_PREFIX;
    $DS_PREFIX = DS_PREFIX;
    $H8_PREFIX = H8_PREFIX;

    $AG_HASHCODE = AG_HASHCODE;
    $BBIN_HASHCODE = BBIN_HASHCODE;
    $DS_HASHCODE = DS_HASHCODE;
    $H8_HASHCODE = H8_HASHCODE;
}
$f = new Fetch(PINGTAI_URL);
//$iswap = CommonClass::is_wap();
$playType = 'PC';
//if($iswap){
//    $playType = 'PM';
//}
switch ($action) {
    case 'ag':
        echo CommonClass::getLoadingPage('ag');
        $plat = CommonClass::filter_input_init(INPUT_GET, 'plat');
        $plat = empty($plat)?'ag_live':$plat;
        $re = $clientA->getProtectStatus(SITE_ID, $plat);
        if ($re['code'] == $objCode->success_get_weihu->code) {
            $protect = json_decode($re['info'],TRUE);
            if(isset($protect['status'])){
                if($protect['status'] == 2){
                    echo "<script>alert('AG贵宾厅维护中，请玩其他游戏！'); self.close();</script>";
                    return false;
                }
            }
        }
        
        $p = array(
            'username' => $AG_PREFIX . $params['username'],
            'password' => AG_PASSWORD,
            'hashcode' => $AG_HASHCODE,
            'keyb' => AG_KEYB,
            'live' => AG_LIVE_TYPE,
            'key' => CommonClass::get_key_param($AG_PREFIX . $params['username'] . AG_PASSWORD . AG_KEYB . date("Ymd"), 6, 9)
        );
        $gametype = CommonClass::filter_input_init(INPUT_GET, 'game_type');
        if(!empty($gametype)){
            $p['gameType'] = $gametype;
        }
        $r = $f->Login($p);
        echo $r;
        break;
    case 'bb':
        echo CommonClass::getLoadingPage('bbin');
        $re = $clientA->getProtectStatus(SITE_ID, 'bb_live');
        if ($re['code'] == $objCode->success_get_weihu->code) {
            $protect = json_decode($re['info'],TRUE);
            if(isset($protect['status'])){
                if($protect['status'] == 2){
                    echo "<script>alert('BBIN旗舰厅维护中，请玩其他游戏！'); self.close();</script>";
                    return false;
                }
            }
        }
        $p = array(
            'username' => $BBIN_PREFIX . $params['username'],
            'password' => BBIN_PASSWORD,
            'hashcode' => $BBIN_HASHCODE,
            'keyb' => BBIN_KEYB,
            'live' => BBIN_LIVE_TYPE,
            'key' => CommonClass::get_key_param($BBIN_PREFIX . $params['username'] . BBIN_PASSWORD . BBIN_KEYB . date("Ymd"), 6, 9),
            'page_site' => 'live'
        );
        $r = $f->Login($p);
        $r = str_replace(array('ERROR..UID', 'Please wait for 3 minutes and re-login'), array("登录错误，请稍后再试！", '登录过于频繁，请三分钟后重新登录'), $r);
        echo $r;
        break;
    case 'bbsport':
        echo CommonClass::getLoadingPage('bbin');
        $re = $clientA->getProtectStatus(SITE_ID, 'bb_sport');
        if ($re['code'] == $objCode->success_get_weihu->code) {
            $protect = json_decode($re['info'],TRUE);
            if(isset($protect['status'])){
                if($protect['status'] == 2){
                    echo "<script>alert('BBIN体育维护中，请玩其他游戏！'); self.close();</script>";
                    return false;
                }
            }
        }
        $p = array(
            'username' => $BBIN_PREFIX . $params['username'],
            'password' => BBIN_PASSWORD,
            'hashcode' => $BBIN_HASHCODE,
            'keyb' => BBIN_KEYB,
            'live' => BBIN_LIVE_TYPE,
            'key' => CommonClass::get_key_param($BBIN_PREFIX . $params['username'] . BBIN_PASSWORD . BBIN_KEYB . date("Ymd"), 6, 9),
            'page_site' => 'ball'
        );
        $r = $f->Login($p);
        $r = str_replace(array('ERROR..UID', 'Please wait for 3 minutes and re-login'), array("登录错误，请稍后再试！", '登录过于频繁，请三分钟后重新登录'), $r);
        echo $r;
        break;
    case 'bbgame':
        //echo CommonClass::getLoadingPage('bbin');
        $re = $clientA->getProtectStatus(SITE_ID, 'bb_game');
        if ($re['code'] == $objCode->success_get_weihu->code) {
            $protect = json_decode($re['info'],TRUE);
            if(isset($protect['status'])){
                if($protect['status'] == 2){
                    echo "<script>alert('BBIN电子游戏维护中，请玩其他游戏！'); self.close();</script>";
                    return false;
                }
            }
        }
        $loginType = CommonClass::filter_input_init(INPUT_GET, 'lt');
        $gameType = CommonClass::filter_input_init(INPUT_GET, 'id');
        $p = array(
            'username' => $BBIN_PREFIX . $params['username'],
            'password' => BBIN_PASSWORD,
            'hashcode' => $BBIN_HASHCODE,
            'keyb' => BBIN_KEYB,
            'live' => BBIN_LIVE_TYPE,
            'key' => CommonClass::get_key_param($BBIN_PREFIX . $params['username'] . BBIN_PASSWORD . BBIN_KEYB . date("Ymd"), 6, 9),
            'page_site' => 'game',
            'loginType'=>$loginType,
            'gameType'=>$gameType
        );
        $r = $f->Login($p);
        echo "<script>location.href='{$r}';</script>";
        //echo $r;
        break;
    case 'ds':
        echo CommonClass::getLoadingPage('ds');
        $re = $clientA->getProtectStatus(SITE_ID, 'ds_live');
        if ($re['code'] == $objCode->success_get_weihu->code) {
            $protect = json_decode($re['info'],TRUE);
            if(isset($protect['status'])){
                if($protect['status'] == 2){
                    echo "<script>alert('DS现场厅维护中，请玩其他游戏！'); self.close();</script>";
                    return false;
                }
            }
        }
        
        if(SITE_TYPE == 1){//主站
            $DS_XG_HANDICP = PK_ZHU_XG_STATUS;
        }else{//副站
            $DS_XG_HANDICP = PK_FU_XG_STATUS;
        }
        
        $p = array(
            'dsGameType'=>'live',
            'username' => $DS_PREFIX . $params['username'],
            'password' => md5(DS_PASSWORD),
            'hashcode' => $DS_HASHCODE,
            'keyb' => DS_KEYB,
            'live' => DS_LIVE_TYPE,
            'playType' => $playType,
            'handicap' => $DS_XG_HANDICP,
            'key' => CommonClass::get_key_param($DS_PREFIX . $params['username'] . md5(DS_PASSWORD) . DS_KEYB . date("Ymd"), 6, 9)
        );
        $r = $f->Login($p);
        $re = json_decode($r, TRUE);
        if ($re['status'] == 10000) {
            echo "<script>location.href='{$re['message']['params']['link']}';</script>";
            //header("location: {$re['message']['params']['link']}");
        } else if ($re['status'] == 10090) {//维护中
            echo "<script>alert('DS平台维护中！'); self.close();</script>";
            return false;
        } else {//登录错误
            echo "<script>alert('登录失败，请稍后再试！'); self.close();</script>";
            return false;
        }
        break;
    case 'dsgame':
        echo CommonClass::getLoadingPage('ds');
        $re = $clientA->getProtectStatus(SITE_ID, 'ds_game');
        if ($re['code'] == $objCode->success_get_weihu->code) {
            $protect = json_decode($re['info'],TRUE);
            if(isset($protect['status'])){
                if($protect['status'] == 2){
                    echo "<script>alert('DS电子游戏维护中，请玩其他游戏！'); self.close();</script>";
                    return false;
                }
            }
        }
        $id = CommonClass::filter_input_init(INPUT_GET, 'id');
        $oid = $clientA->getSessionKeyforDsGame(SITE_ID,$params['username']);
        $url = DS_GAME_HOST."&gameId={$id}&sessionKey={$oid}&language=zh";
        if ($session['user_type'] == 11 || $session['user_type'] == 21) {//主副站修改
            $url = DS_GAME_HOST_TEST."&gameId={$id}&sessionKey={$oid}&language=zh";
        }
        echo "<script>location.href='{$url}';</script>";
        break;
    case 'h8':
        $re = $clientA->getProtectStatus(SITE_ID, 'hb_sport');
        if ($re['code'] == $objCode->success_get_weihu->code) {
            $protect = json_decode($re['info'],TRUE);
            if(isset($protect['status'])){
                if($protect['status'] == 2){
                    echo 10090;
                    return false;
                }
            }
        }
        
        $p = array(
            'username' => $H8_PREFIX . $params['username'],
            'password' => H8_PASSWORD,
            'hashcode' => $H8_HASHCODE,
            'keyb' => H8_KEYB,
            'live' => H8_LIVE_TYPE,
            'key' => CommonClass::get_key_param($H8_PREFIX . $params['username'] . H8_PASSWORD . H8_KEYB . date("Ymd"), 6, 9)
        );

        $r = $f->Login($p);
        echo $r;
        break;
    case 'lotto'://小鱼
        $re = $clientA->getProtectStatus(SITE_ID, 'ds_lotto_jd');
        if ($re['code'] == $objCode->success_get_weihu->code) {
            $protect = json_decode($re['info'],TRUE);
            if(isset($protect['status'])){
                if($protect['status'] == 2){
                    echo "<script>alert('DS电子游戏维护中，请玩其他游戏！'); self.close();</script>";
                    return false;
                }
            }
        }
        $str = $params['company'] . '_' . $params['username'] . '_' . $params['oid'];
        $key = $clientA->getKeyForLotto($str);
        $r = LOTTO_HOST.$key;
        echo "<script>location.href='{$r}';</script>";
        return false;
        break;
    case 'ds_lottery':
        $re = $clientA->getProtectStatus(SITE_ID, 'ds_lottery');
        if ($re['code'] == $objCode->success_get_weihu->code) {
            $protect = json_decode($re['info'],TRUE);
            if(isset($protect['status'])){
                if($protect['status'] == 2){
                    echo "#";
                    return false;
                }
            }
        }
        if(SITE_TYPE == 1){//主站
            $DS_SS_HANDICP = PK_ZHU_SS_STATUS;
        }else{//副站
            $DS_SS_HANDICP = PK_FU_SS_STATUS;
        }
        $p = array(
            'dsGameType'=>'lottery', //时时彩
            'username' => $DS_PREFIX . $params['username'],
            'password' => md5(DS_PASSWORD),
            'hashcode' => $DS_HASHCODE,
            'keyb' => DS_KEYB,
            'live' => DS_LIVE_TYPE,
            'playType' => $playType,
            'handicap' => $DS_SS_HANDICP,
            'key' => CommonClass::get_key_param($DS_PREFIX . $params['username'] . md5(DS_PASSWORD) . DS_KEYB . date("Ymd"), 6, 9)
        );
//        $f->debug = true;
        $r = $f->Login($p);
        $re = json_decode($r, TRUE);
        
//        echo "<pre>";
//        print_r($r);
//        echo "<br />";
//        print_r($p);
//        echo "</pre>";
        
        if(isset($re['status'])){
            if ($re['status'] == 10000) {
                echo $re['message']['params']['link'];
            } else if ($re['status'] == 10090) {//维护中
                echo 10090;
                return false;
            } else {//登录错误
                echo 10090;
                return false;
            }
        }else{
            echo 10090;
        }
        break;
    case 'ds_lotto':
        $re = $clientA->getProtectStatus(SITE_ID, 'ds_lotto');
        if ($re['code'] == $objCode->success_get_weihu->code) {
            $protect = json_decode($re['info'],TRUE);
            if(isset($protect['status'])){
                if($protect['status'] == 2){
                    echo "#";
                    return false;
                }
            }
        }
        if(SITE_TYPE == 1){//主站
            $DS_XG_HANDICP = PK_ZHU_XG_STATUS;
        }else{//副站
            $DS_XG_HANDICP = PK_FU_XG_STATUS;
        }
        $p = array(
            'dsGameType'=>'lotto', //香港彩
            'username' => $DS_PREFIX . $params['username'],
            'password' => md5(DS_PASSWORD),
            'hashcode' => $DS_HASHCODE,
            'keyb' => DS_KEYB,
            'live' => DS_LIVE_TYPE,
            'playType' => $playType,
            'handicap' => $DS_XG_HANDICP,
            'key' => CommonClass::get_key_param($DS_PREFIX . $params['username'] . md5(DS_PASSWORD) . DS_KEYB . date("Ymd"), 6, 9)
        );
        $r = $f->Login($p);
        $re = json_decode($r, TRUE);
        
        if(isset($re['status'])){
            if ($re['status'] == 10000) {
                echo $re['message']['params']['link'];
            } else if ($re['status'] == 10090) {//维护中
                echo 10090;
                return false;
            } else {//登录错误
                echo 10090;
                return false;
            }
        }else{
            echo 10090;
        }
        break;
    case 'fenfen':
        $re = $clientA->getProtectStatus(SITE_ID, 'ds_fenfen');
        if ($re['code'] == $objCode->success_get_weihu->code) {
            $protect = json_decode($re['info'],TRUE);
            if(isset($protect['status'])){
                if($protect['status'] == 2){
                    echo "#";
                    return false;
                }
            }
        }
        $baseApiUrl = str_replace('&amp;', '&', FENFEN_HOST);
        if ($session['user_type'] == 11 || $session['user_type'] == 21) {//主副站修改
            //基础url
            $baseApiUrl = str_replace('&amp;', '&', FENFEN_HOST_TEST);
        }
        //平台接入代号
        $dcCustomerId = 'companyds';
        // 平台加密串应该保密
        $dcCustomerSec = FENFEN_SCREAT_KEY;
        // 请求的平台的链接token 一般用 time() 就行
        $dcToken = time();
        // 平台的用户名
        $dcUsername = $params['username'];
        // 平台的用户所属的 网站id
        //$dcSiteId = $params['company'];
        $dcSiteId = SITE_ID;
        
        //用户的级别树。方便分等级统计数据。 agent,world,corprator,super
        $dcUserTree="{$session['super']},{$session['corprator']},{$session['world']},{$session['agent']}" ;

        // 平台的用户所属的公司 默认是 1 //鼎盛
        //$dcCompany = 1;

        //平台的用户所属的股东 默认是 2 鼎盛股东 非必须
        //$dcShareholder = 2;

        // 平台的用户所属的代理 默认是 3 鼎盛代理
        //$dcAgentId = 3;
        
        $dcUserType = 2;
        if (substr($session['user_type'], -1) == 1) {//主副站修改
            $dcUserType = 3;
        }

        //平台的用户登录之后的默认游戏 cqssc表示 重庆时时彩 其他的看资料
        $dcFirstGame = FENFEN_FIRST_GAME;
        
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $is_iphone = (strpos($agent, 'iPhone')) ? 'true' : 'false';
        $is_ipad = (strpos($agent, 'iPad')) ? 'true' : 'false';
        
        $params = "&dcUserType={$dcUserType}&dcCustomerId={$dcCustomerId}&dcToken={$dcToken}&dcUsername={$dcUsername}&dcSiteId={$dcSiteId}&dcUserTree={$dcUserTree}&dcFirstGame={$dcFirstGame}&iph={$is_iphone}&ipa={$is_ipad}";
        $dcEncryptStr = CommonClass::dcEncrypt("dcCustomerId={$dcCustomerId}&dcToken={$dcToken}&dcUsername={$dcUsername}&dcSiteId={$dcSiteId}", $dcCustomerSec);
        
        //$params = "&dcUserType={$dcUserType}&dcCustomerId={$dcCustomerId}&dcToken={$dcToken}&dcUsername={$dcUsername}&dcSiteId={$dcSiteId}&dcCompany={$dcCompany}&dcShareholder={$dcShareholder}&dcAgentId={$dcAgentId}&dcFirstGame={$dcFirstGame}";
        // 验证加密 只需要按照顺序 dcCustomerId dcToken dcUsername dcSiteId 
        //$dcEncryptStr = CommonClass::dcEncrypt("dcCustomerId={$dcCustomerId}&dcToken={$dcToken}&dcUsername={$dcUsername}&dcSiteId={$dcSiteId}", $dcCustomerSec);
        $params.='&dcEncrypt=' . $dcEncryptStr;
        // 最后的登录url
        $loginUrl = $baseApiUrl . $params;
        echo $loginUrl;
        break;
    case 'og':
        break;
}
?>

