<?php
header('Access-Control-Allow-Origin:*');
ini_set("display_errors", "on");
require_once("config.php");
require_once("Short_hand.php");//common used class by common function
require_once("api_common.php");//common function used in api
require_once("payment_invoker.php");
switch ($action) {
    case 'register'://会员注册
        $config = CommonClass::getSiteConfig($clientA);
        if (CAN_REGISTER == 4 || (CAN_REGISTER == 2 && SITE_TYPE == 1) || (CAN_REGISTER == 3 && SITE_TYPE == 2)) {
            $re = [
                'fail_to_reg_not_notallow' => $objCode->fail_to_reg->code,
                'errMsg' => '网站类型错误'
            ];
            break;
        }
        $get_input = ['username', 'password', 'agree', 'intr', 'sp', 'unionid', 'nickname'];
        $neglect = ['intr', 'sp', 'unionid', 'nickname'];
        $re = check_param($get_input, $params, $neglect);
        if ($re !== true) {
            break;
        }
        extract($params);
        $mobile = null;
        $truename = '';
        if (!empty($nickname) && !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]{2,8}$/u", $nickname)) {
            $re = [
                'code' => $objCode->fail_to_reg->code,
                'errMsg' => '昵称必须是2-8位的中文、字母和数字'
            ];
            break;
        }
        if (!empty($unionid)) {
            $username = $unionid;
            $password = $unionid . $loginkeyb;
        } else {
            if (!CommonClass::check_username_str($username, 4, 12)) {
                $re = [
                    'code' => $objCode->fail_to_reg->code,
                    'errMsg' => '用户名必须为4-12位之间的字母和数字'
                ];
                break;
            }
            if (!CommonClass::check_password_str($password, 8, 12)) {
                $re = [
                    'code' => $objCode->fail_to_reg->code,
                    'errMsg' => '登录密码必须为8-12位之间的字母和数字'
                ];
                break;
            }
        }
        $passwd = $password;
        if (
            empty($username) ||
//                empty($truename) ||
//                empty($mobile) ||
            empty($password) ||
            empty($passwd) ||
            empty($agree) ||
            $password != $passwd
//               || !CommonClass::check_mobile_str($mobile)
        ) {
            $re = [
                'code' => $objCode->fail_to_reg->code,
                'errMsg' => '用户名或者密码不能为空'
            ];
            break;
        }
        //验证用户名存在
        $data1 = [
            'username' => $username,
            'company' => SITE_ID,
            'ip' => $ip
        ];
        $re1 = goto_center_api('checkUser', $data1);
        if ($re1['code'] == $objCode->is_have_username->code) {
            $re = [
                'code' => $objCode->fail_to_reg->code,
                'errMsg' => '用户名已存在'
            ];
            break;
        }
        $pwd = CommonClass::get_md5_pwd($password);
        $params = [
            'username' => $username,
            'userpass' => $pwd,
            'phone' => $mobile,
            'mobile' => $mobile,
            'realname' => $truename,
            'company' => SITE_ID,
            'loginip' => $ip,
            'last_ip' => $ip,
            'ip' => $ip,
            'site_type' => SITE_TYPE,//主副站修改、
            'nickname' => $nickname//昵称
        ];
        if (!empty($intr)) {
            $params['agid'] = $intr;
        }
        if (!empty($sp)) {
            $params['sp'] = $sp;
        }
        if (!empty($email)) {
            if (!preg_match("/^([a-z0-9A-Z]+[-|\\.]?)+[a-z0-9A-Z]@([a-z0-9A-Z]+(-[a-z0-9A-Z]+)?\\.)+[a-zA-Z]{2,}$/", $email)) {
                $re = [
                    'code' => $objCode->errMsgApp->code,
                    'errMsg' => '请输入正确的电子邮箱'
                ];
                break;
            }
            $params['email'] = $email;
        }
        if (!empty($qq)) {
            if (!preg_match("/^[1-9][0-9]{4,11}$/", $qq)) {
                $re = [
                    'code' => $objCode->fail_to_agentreg->code,
                    'errMsg' => '请输入正确的qq号'
                ];
                break;
            }
            $params['qq'] = $qq;
        }
        $params['host'] = $host;
        $re = goto_center_api('userRegister', $params);
        if ($re['code'] == $objCode->success_to_reg_and_login->code) {
            $re['data'] = json_decode($re['info'], TRUE);
            $re['data']['last_login_time'] = date("y-m-d H:i:s");
            $re['data']['supers'] = $re['data']['super'];
            $v_k = 'VIP' . $re['data']['uvip'];
            $re['data']['uvipname'] = $config['api_config'][$v_k];
            unset($re['data']['super'], $re['info']);
            $pa['siteId'] = SITE_ID;
            $pa['username'] = $username;
            $pa['agents'] = $re['data']['agent'];
            $pa['world'] = $re['data']['world'];
            $pa['corprator'] = $re['data']['corprator'];
            $pa['superior'] = $re['data']['supers'];
            $pa['company'] = 'admin';
            $demo_user = [
                '1' => '11',
                '2' => '21'
            ];
            $site_type = SITE_TYPE;//主副站修改
            if ($re['data']['user_type'] == $demo_user[$site_type]) {//主副站修改
            } else {
                //MY_TCP_MONEY_HOST_TEST//光光
                //MY_TCP_MONEY_HOST//光光临时注释
                $x = goto_coffee_mix('setMemberData', MY_TCP_MEMBER_HOST, json_encode($pa));//光光临时注释
//                "{message=ok, code=100000}"
            }
        } else {
            $re['data'] = json_decode($re['info'], TRUE);
            unset($re['info']);
        }
        break;
    case 'agentregister'://代理注册
        $username = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        if (!empty($username)) {
            $username = 'd' . $username;
        }
        $truename = CommonClass::filter_input_init(INPUT_POST, 'realname', FILTER_SANITIZE_MAGIC_QUOTES);
        $mobile = CommonClass::filter_input_init(INPUT_POST, 'mobile', FILTER_SANITIZE_MAGIC_QUOTES);
        $password = CommonClass::filter_input_init(INPUT_POST, 'password', FILTER_SANITIZE_MAGIC_QUOTES);
        $passwd = CommonClass::filter_input_init(INPUT_POST, 'passwd', FILTER_SANITIZE_MAGIC_QUOTES);
        $agree = CommonClass::filter_input_init(INPUT_POST, 'agree', FILTER_SANITIZE_MAGIC_QUOTES);
        $bankname = CommonClass::filter_input_init(INPUT_POST, 'bankname', FILTER_SANITIZE_MAGIC_QUOTES);
        $bankaccount = CommonClass::filter_input_init(INPUT_POST, 'bankaccount', FILTER_SANITIZE_MAGIC_QUOTES);
        $bankaddress = CommonClass::filter_input_init(INPUT_POST, 'bankaddress', FILTER_SANITIZE_MAGIC_QUOTES);
        $email = CommonClass::filter_input_init(INPUT_POST, 'email', FILTER_SANITIZE_MAGIC_QUOTES);
        $qq = CommonClass::filter_input_init(INPUT_POST, 'qq', FILTER_SANITIZE_MAGIC_QUOTES);
        if (!empty($qq) && !preg_match("/^[1-9][0-9]{4,11}$/", $qq)) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'errMsg' => '请输入正确的qq号'];
            break;
        }
        if (empty($username)) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '用户名不能为空'];
            break;
        }
        if (empty($truename)) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '真实姓名必须填写'];
            break;
        }
        if (empty($mobile)) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '手机号码必须填写'];
            break;
        }
        if (
        empty($password)
        ) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '密码必须填写'];
            break;
        }
        if (
        empty($passwd)
        ) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '第二次密码不能为空！'];
            break;
        }
        if (
        empty($bankname)
        ) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '银行名称必须选择'];
            break;
        }
        if (
        empty($bankaccount)
        ) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '银行账号必须填写'];
            break;
        }
        if (
            $password != $passwd
        ) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '请输入相同的两次密码！'];
            break;
        }
        if (
        !CommonClass::check_agentname_str($username, 4, 12)
        ) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '用户名必须为4-12位之间的字母和数字'];
            break;
        }
        if (
        !CommonClass::check_password_str($password, 8, 12)
        ) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '登录密码必须为8-12位之间的字母和数字'];
            break;
        }
        if (
        !CommonClass::check_mobile_str($mobile)
        ) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '请填写正确的手机号码'];
            break;
        }
        //验证用户名存在
        $data1 = ['username' => $username, 'company' => SITE_ID, 'ip' => $ip];
        $re1 = goto_center_api('checkAgentUser', $data1);
        if ($re1['code'] == $objCode->is_have_username->code) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'tips' => 2, 'errMsg' => '用户名已经存在'];
            break;
        }
        //验证真实姓名存在
        $data2 = ['realname' => $truename, 'company' => SITE_ID, 'ip' => $ip];
        $re2 = goto_center_api('checkAgentTrueName', $data2);
        if ($re2['code'] == $objCode->is_have_truename->code) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'tips' => 3, 'errMsg' => '真实姓名已经存在'];
            break;
        }
        //验证手机号码存在
        $data3 = ['mobile' => $mobile, 'company' => SITE_ID, 'ip' => $ip];
        $re3 = goto_center_api('checkAgentMobile', $data3);
        if ($re3['code'] == $objCode->is_have_mobile->code) {
            $re = ['code' => $objCode->fail_to_agentreg->code, 'tips' => 4, 'errMsg' => '手机号码已经存在'];
            break;
        }
        //后期银行卡验证 //TODO
        $pwd = CommonClass::get_md5_pwd($password);
        $params = [
            'username' => $username,
            'userpass' => $pwd,
            'phone' => $mobile,
            'mobile' => $mobile,
            'realname' => $truename,
            'site_id' => SITE_ID,
            'loginip' => $ip,
            'ip' => $ip,
            'bank_id' => $bankname,
            'bank_adress' => $bankaddress,
            'bank_account' => $bankaccount,
            'site_type' => SITE_TYPE          //主副站修改
        ];
        if (!empty($email)) {
            if (!preg_match("/^([a-z0-9A-Z]+[-|\\.]?)+[a-z0-9A-Z]@([a-z0-9A-Z]+(-[a-z0-9A-Z]+)?\\.)+[a-zA-Z]{2,}$/", $email)) {
                $re = ['code' => $objCode->fail_to_agentreg->code, 'errMsg' => '请输入正确的电子邮箱'];
                break;
            }
            $params['email'] = $email;
        }
        if (!empty($qq)) {
            $params['qq'] = $qq;
        }
        $re = goto_center_api('agentRegister', json_encode($params));
        $re['tips'] = 5;
        $pa['siteId'] = SITE_ID;
        $pa['username'] = $re['Hierarchy']['username'];
        $pa['agents'] = $re['Hierarchy']['agents'];
        $pa['world'] = $re['Hierarchy']['world'];
        $pa['corprator'] = $re['Hierarchy']['corprator'];
        $pa['superior'] = $re['Hierarchy']['superior'];
        $pa['company'] = 'admin';
        $demo_agent = ['1' => '10', '2' => '20'];
        $site_type = SITE_TYPE; //主副站修改
        if ($re['Hierarchy']['user_type'] == $demo_agent[$site_type]) {//主副站修改
            $config = CommonClass::getSiteConfig($clientA);
            $x = goto_coffee_mix('setMemberData', MY_TCP_MONEY_HOST, json_encode($pa)); //光光
        }
        unset($re['Hierarchy']);
        break;
    case 'checpwd'://会员修改密码验证旧密码
        $get_input = ['username', 'ypassword'];
        $re = check_param($get_input, $params, $neglect);
        if ($re !== true) {
            return $re;
        }
        $password = CommonClass::get_md5_pwd($params['ypassword']);
        $chk_pswd = [
            'username' => $params['username'],
            'company' => SITE_ID,
            'password' => $password,
            'ip' => $ip
        ];
        $re = goto_center_api('checkPassword', $chk_pswd);
        break;
    case 'checkreg'://会员注册异步校验
        $h_instance = new Harris_Sanitize();
        $_POST = $h_instance->sanitize($_POST);
        $rules = [
            'username' => 'required|alpha_numeric|max_len,12|min_len,3',
            'ausername' => 'alpha_numeric|max_len,12|min_len,3',
            'arealname' => 'alpha_numeric|max_len,12|min_len,3',
            'amobile' => 'phone_number',
            'aemail' => 'valid_email|min_len,6',
            'aqq' => 'valid_email|min_len,9',
        ];
        $filters = [
            'username' => 'trim|sanitize_string|strtolower',
            'ausername' => 'trim|sanitize_string|strtolower',
            'arealname' => 'trim|sanitize_string|strtolower',
            'amobile' => 'trim',
            'aemail' => 'trim|sanitize_email',
            'aqq' => 'trim',
            'abankaccount' => 'trim',
        ];
        $_POST = $h_instance->filter($_POST, $filters);
        $validated = $h_instance->validate($_POST, $rules);
        if ($validated !== true) {
            $declare_chinese = [
                'username' => '用户名',
                'ausername' => '用户名',
                'arealname' => '用户真实名',
                'amobile' => '手机号码',
                'aemail' => '邮箱',
                'aqq' => 'QQ号',
                'abankaccount' => '银行账号',
            ];
            $v_error = $h_instance->get_readable_errors();
            $v_error = $h_instance->h_ack_message($v_error);
            $v_error = $h_instance->h_ack_translate($declare_chinese, $v_error);
            $re['code'] = $objCode->paramerror1->code;
            $re['errMsg'] = $v_error;
        } else {
            $username = $_POST['ausername'];
            $truename = $_POST['arealname'];
            $mobile = $_POST['amobile'];
            $email = $_POST['aemail'];
            $qq = $_POST['aqq'];//^[1-9][0-9]{4,11}$/
            $bankaccount = $_POST['abankaccount'];
            $nowusername = $_POST['username'];
            if ($username) {
                $params = [
                    'username' => $username,
                    'company' => SITE_ID,
                    'ip' => $ip
                ];
                $re = goto_center_api('checkUser', $params);
            } else if ($truename) {
                $params = [
                    'realname' => $truename,
                    'company' => SITE_ID,
                    'ip' => $ip
                ];
                $re = goto_center_api('checkTrueName', $params);
            } else if ($mobile) {
                $params = [
                    'mobile' => $mobile,
                    'company' => SITE_ID,
                    'ip' => $ip
                ];
                $re = goto_center_api('checkMobile', $params);
            } else if ($email) {
                $params = [
                    'email' => $email,
                    'company' => SITE_ID,
                    'ip' => $ip
                ];
                $re = goto_center_api('checkEmail', $params);
            } else if ($qq) {
                $params = [
                    'qq' => $qq,
                    'company' => SITE_ID,
                    'ip' => $ip
                ];
                $re = goto_center_api('checkQq', $params);
            } else if ($bankaccount) {
                $params = [
                    'bankaccount' => $bankaccount,
                    'company' => SITE_ID,
                    'ip' => $ip,
                    'username' => $nowusername
                ];
                $re = goto_center_api('checkBankaccount', $params);
            }
        }
        //##########################################################################################
        break;
    case 'checpwdget'://会员修改出款密码验证旧密码
        $get_input = ['username', 'ypassword'];
        $re = check_param($get_input, $params, $neglect);
        if ($re !== true) {
            return $re;
        }
        $password = CommonClass::get_md5_pwd($params['ypassword']);
        $chk_pswd = [
            'username' => $params['username'],
            'company' => SITE_ID,
            'password' => $password,
            'ip' => $ip
        ];
        $re = goto_center_api('checkPasswordGet', $chk_pswd);
        break;
    case 'login'://登录
        $get_input = ['username', 'password', 'unionid', 'varcode'];
        $neglect = ['unionid', 'varcode'];
        $re = check_param($get_input, $params, $neglect);
        if ($re !== true) {
            break;
        }
        $unionid = $params['unionid'];
        $username = $params['username'];
        $password = $params['password'];
        if (!empty($unionid)) {
            $username = $unionid;
            $password = $unionid . $loginkeyb;
        }
        $config = CommonClass::getSiteConfig($clientA);
        if ($username == -1) {
            //试玩
            $test_play = [
                'company' => SITE_ID,
                'ip' => $ip,
                'user_type' => SITE_TYPE
            ];
            $re = goto_center_api('testPlayLogin', $test_play);//主副站修改
        } else {
            $pwd = CommonClass::get_md5_pwd($password);
            $vcode = $params['varcode'];
            if (NEED_CODE == 1) {//需要验证码
                session_start();
                if ($vcode != (isset($_SESSION['vcode_session']) ? $_SESSION['vcode_session'] : null) || empty($vcode)) {
                    $re['code'] = $objCode->fail_to_login_error_code->code;
                    break;
                }
            }
            $real_play = [
                'username' => $username,
                'company' => SITE_ID,
                'userpass' => $pwd,
                'ip' => $ip,
                'user_type' => SITE_TYPE
            ];//主副站修改
            $re = goto_center_api('login', $real_play);
        }
        if ($re['code'] == $objCode->success_to_login->code) {
            CommonClass::myLog($username . '登录');
            include_once 'clientGetObj.php';
            $os = new clientGetObj();
            $record_client_data = [
                'site_id' => SITE_ID,
                'username' => $username,
                'os' => $os->get_os(),
                'browse' => $os->get_broswer(),
                'ip' => $ip,
                'addtime' => time()
            ];
            $record_client_result = goto_center_api('recordClient', $record_client_data);
            if ($record_client_result !== true) {
                $re['errMsg'] = '插入用户详情数据失败';
                break;
            }
            $re['data'] = json_decode($re['info'], TRUE);
            $re['data']['last_login_time'] = date("y-m-d H:i:s");
            $re['data']['supers'] = $re['data']['super'];
            $v_k = 'VIP' . $re['data']['uvip'];
            $re['data']['uvipname'] = $config['api_config'][$v_k];
            unset($re['data']['super'], $re['info']);
        }
        break;
    case 'getnotice'://获取公告信息
        $get_input = ['note_type'];
        $re = prepare_goto_hprose($get_input, 'getNotice', $params);
        if ($re['code'] == $objCode->success_get_notice->code) {
            $notice = $re['notice'];
            unset($re['notice']);
            $re['data'] = [
                'notice' => json_decode($notice)
            ];
        }
        break;
    case 'getnoticehistory'://获取历史公告信息
        $get_input = ['note_type'];
        $re = prepare_goto_hprose($get_input, 'getNoticeHistory', $params);
        if ($re['code'] == $objCode->success_get_history_notice->code) {
            $re['data'] = [
                'notice' => json_decode($re['info'])
            ];
        }
        break;
    case 'getmessage'://获取短信息
        $params['company'] = SITE_ID;
        $get_input = ['username', 'oid'];
        $re = prepare_goto_hprose($get_input, 'getMessage', $params);
        /*if ($re['code'] == $objCode->success_get_message->code) {
            $re['data'] = array('message' => json_decode($re['info']));
        }*/
        break;
    case 'setmessage'://修改短信息
        $get_input = ['username', 'id'];
        $params['company'] = SITE_ID;
        $re = prepare_goto_hprose($get_input, 'setMessage', $params);
        if ($re['code'] == $objCode->success_set_message->code) {
            $re['data'] = [
                'message' => $re['info']
            ];
        }
        break;
    case 'getcountmessage'://获取短信息未读条数
        $params['company'] = SITE_ID;
        $get_input = ['username'];
        $re['count'] = prepare_goto_hprose($get_input, 'getCountMessage', $params);
        $re['code'] = '1000';
        break;
    case 'logout'://登出
        $get_input = ['username'];
        $params['company'] = SITE_ID;
        $params['ip'] = $ip;
        $re = prepare_goto_hprose($get_input, 'logout', $params);
        break;
    case "getbanks"://获取银行列表
        $re = goto_center_api('getAllBankInfo');
        if ($re['code'] == $objCode->success_get_banks->code) {
            $re['data'] = [
                'banks' => json_decode($re['info'])
            ];
            unset($re['info']);
        }
        break;
    case "protect"://获取维护状态
//        if(SITE_ID == 1001){
//            $dt = date("Y-m-d H:i:s");
//            if($dt < '2016-02-08 09:00:00' && $dt > '2016-02-07 05:00:00'){
//                $re['code'] = 201616;
//                break;
//            }
//        }
        //ip限制
//       include("geoip.inc.php");
        // 打开数据文件
//        $gi = geoip_open("GeoIP.dat",GEOIP_STANDARD);
        // 获取国家代码
//        $country_code = geoip_country_code_by_addr($gi, $ip);
//        if($country_code == 'PH' || $country_code == 'HK' || $country_code == 'MO'){
//            $re = array();
//            $re['code'] = 7007;
//            $re['data'] = array('country_code' => $country_code);
//
//            geoip_close($gi);
//            break;
//        }
//        geoip_close($gi);
        $config = CommonClass::getSiteConfig($clientA);
        $tag = CommonClass::filter_input_init(INPUT_POST, 'tag', FILTER_SANITIZE_MAGIC_QUOTES);
        $aprotect = CommonClass::filter_input_init(INPUT_POST, 'aprotect', FILTER_SANITIZE_MAGIC_QUOTES);//是否所有维护状态
        $re = goto_center_with_paras('getProtectStatus', SITE_ID, $tag, $aprotect);
        $allregister = 1;//允许注册
        if (CAN_REGISTER == 4 || (CAN_REGISTER == 2 && SITE_TYPE == 1) || (CAN_REGISTER == 3 && SITE_TYPE == 2)) {
            $allregister = 2;//关闭注册功能
        }
        if ($re['code'] == $objCode->success_get_weihu->code) {
            $re['data'] = [
                'protect' => json_decode($re['info']),
                'vcode' => NEED_CODE,
                'allregister' => $allregister
            ];
        }
        break;
    case 'changepassword'://修改密码
        $h_instance = new Harris_Sanitize();
        $_POST = $h_instance->sanitize($_POST);
        $rules = [
            'username' => 'required|alpha_numeric|max_len,12|min_len,3',
            'ypassword' => 'required|max_len,12|min_len,8',
            'password' => 'required|max_len,12|min_len,8',
            'oid' => 'required',
            'user_type' => 'required|max_len,2|min_len,1',
        ];
        $filters = [
            'username' => 'trim|sanitize_string',
            'ypassword' => 'trim',
            'password' => 'trim',
            'oid' => 'trim',
            'user_type' => 'trim',
        ];
        $_POST = $h_instance->filter($_POST, $filters);
        $validated = $h_instance->validate($_POST, $rules);
        if ($validated !== true) {
            $declare_chinese = [
                'username' => '用户名',
                'ypassword' => '旧密码',
                'password' => '新密码',
                'oid' => '用户唯一识别码',
                'User Type' => '用户类型',
            ];
            $v_error = $h_instance->get_readable_errors();
            $v_error = $h_instance->h_ack_message($v_error);
            $v_error = $h_instance->h_ack_translate($declare_chinese, $v_error);
            $re = [
//                'code'=>$objCode->general_error->code,
                'code' => 10001,
                'errMsg' => $v_error,
            ];
        } else {
            $update_password = [
                'username' => $_POST['username'],//$username,
                'userpass' => $_POST['ypassword'],//$pwd,
                'newuserpass' => $_POST['password'],// $newpwd,
                'company' => SITE_ID,
                'oid' => $_POST['oid'],
                'ip' => $ip,
                'user_type' => $_POST['user_type']//$user_type
            ];//主副站修改
            $re = goto_center_api('updatePassword', $update_password);
        }
        break;
    case 'changegetpassword'://修改密码
        $username = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $oid = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $ypassword = CommonClass::filter_input_init(INPUT_POST, 'ypassword', FILTER_SANITIZE_MAGIC_QUOTES);
        $params = [
            'username' => $username,
            'company' => SITE_ID,
            'oid' => $oid,
            'user_type' => $user_type
        ];//主副站修改
        $result = goto_center_api('checkGetPassword', $params);
        if (empty($ypassword)) {
            //**首次还没有设置密码时越过这里，第二次改密码时 旧密码不能为空
            if (empty($result) || !empty($result[0]['get_password'])) {
                $re = [
                    'code' => $objCode->fail_to_change_pwd->code,
                    'errMsg' => '原密码不能为空'
                ];
                break;
            }
        }
        $pwd = CommonClass::get_md5_pwd($ypassword);
        $old_pass_trace = $result[0]['get_password'];
        $password = CommonClass::filter_input_init(INPUT_POST, 'password', FILTER_SANITIZE_MAGIC_QUOTES);
        if (empty($password)) {
            $re = [
                'code' => $objCode->fail_to_change_pwd->code,
                'errMsg' => '现密码不能为空'
            ];
            break;
        } else if (strlen($password) < 4 || strlen($password) > 12) {
            $re = [
                'code' => $objCode->fail_to_change_pwd->code,
                'errMsg' => '请设置现密码为4-12位之间'
            ];
            break;
        }
        $newpwd = CommonClass::get_md5_pwd($password);
        $user_type = CommonClass::filter_input_init(INPUT_POST, 'user_type', FILTER_SANITIZE_MAGIC_QUOTES);//主副站修改
        if (empty($user_type)) {
            $re = [
                'code' => $objCode->fail_to_change_pwd->code,
                'errMsg' => 'user_type不能为空'
            ];
            break;
        }
        $params = [
            'username' => $username,
            'userpass' => $pwd,
            'old_pass_trace' => $old_pass_trace,
            'newuserpass' => $newpwd,
            'company' => SITE_ID,
            'oid' => $oid,
            'ip' => $ip,
            'user_type' => $user_type
        ];//主副站修改
        $re = goto_center_api('updateGetPassword', $params);
        break;
    case 'getbankset'://获取收款银行信息
        $get_input = ['username', 'oid'];
        $re = prepare_goto_hprose($get_input, 'getBanksByUserLevel', $params);
        if ($re['code'] == $objCode->success_get_bank_set->code) {
            $re['data'] = [
                'bankset' => json_decode($re['info'])
            ];
            unset($re['info']);
        }
        break;
    case 'getbillno'://获取出款订单
        $get_input = ['username', 'oid'];
        $re = prepare_goto_hprose($get_input, 'getBillno', $params);
        if ($re['code'] == $objCode->success_get_billno->code) {
            $re['data'] = [
                'billno' => $re['info']
            ];
        }
        break;
    case 'getgetmoneyuserdetail'://获取收款银行信息
        $get_input = ['oid', 'username', 'flag', 'agent'];//2为手机必填，3为单独验证手机
        if (is_array($_REQUEST['agent']) && in_array($agent, ['ddm000', 'ddm000k'])) {
            $re = [
                'code' => $objCode->errMsgApp->code,
                'errMsg' => '此代理下的用户没有提款权限'
            ];
            break;
        }
        $isAlow = prepare_goto_hprose($get_input, 'isAlowSaveTakeFromAgent', $params);//判断允许存取款
        if ($isAlow == 2) {//不允许存取款
            echo CommonClass::ajax_return([
                'code' => $objCode->is_not_allow_save->code
            ], $jsonp, $jsonpcallback);
            exit();
        }
        //#######################################################################
        // 判断提款权限
        $kilo['company'] = SITE_ID;
        $kilo['username'] = $params['username'];
        $session = goto_center_api('getSession', $kilo);
        if ($session['code'] == 201018) {
            $res = json_decode($session['info']);
            $money_status = $res->money_status;
            if ($money_status == 2) {
                $re = [
                    'code' => $objCode->is_not_allow_save->code,
                    'errMsg' => '您的存/取款操作已被停用，请联系客服'
                ];
                break;
            }
        }
        //#######################################################################
        /*else {
            $re = array('code' => $objCode->errMsgApp->code, 'errMsg' => '用户未登录');
            break;
        }*/
        $flag = $params['flag'];
        if ($flag == 2 || $flag == 3) {
            $re = goto_center_with_paras('getGetMoneyUserDetail', $params, $flag);
        } else {
            $re = goto_center_api('getGetMoneyUserDetail', $params);
        }
        $re['data'] = json_decode($re['info']);
        unset($re['info']);
        break;
//测试用
    case 'getmemberinfo':
        try {
            $pwd = CommonClass::get_md5_pwd('234567');
            $newpwd = CommonClass::get_md5_pwd('234567');
            $params = [
                'username' => 'ceshi997',
                'userpass' => $pwd,
                'newuserpass' => $newpwd,
                'company' => 1,
                'ip' => $ip
            ];
            $re = goto_center_api('getMemberInfo', $params);
        } catch (Exception $e) {
            print_r($e);
        }
        print_r($re);
        break;
    case 'savebankorder'://提交公司存款订单
        $h_instance = new Harris_Sanitize();
        $_POST = $h_instance->sanitize($_POST);
        $rules = [
            'username' => 'required|alpha_numeric|max_len,12|min_len,3',
            'oid' => 'required',
            'ulevel' => 'required|max_len,2|min_len,1',
            'out_bank_id' => 'required|integer|max_len,2|min_len,1', //存款人银行id bank_id
            'in_bank_set_id' => 'required|integer|max_len,2|min_len,1',//收款人配置id bank_set_id
            'billno' => 'required|alpha_numeric',
            'user' => 'required',//存款人真实姓名card_user
            'type' => 'required',//支付类型 pay_type
            'money' => 'required|float|regex,/^\d+(\.\d+)?$/', ///^\d+(\.\d+)?$/'
            'useraddtime' => 'required|date', //user_addtime
        ];
        $filters = [
            'username' => 'trim|sanitize_string|strtolower',
            'oid' => 'trim',
            'ulevel' => 'trim',
            'out_bank_id' => 'trim',
            'in_bank_set_id' => 'trim',
            'billno' => 'trim',
            'user' => 'trim',
            'type' => 'trim',
            'money' => 'trim',
            'useraddtime' => 'trim',
        ];
        $_POST = $h_instance->filter($_POST, $filters);
        $validated = $h_instance->validate($_POST, $rules);
        if ($validated !== true) {
            $declare_chinese = [
                'username' => '用户名',
                'oid' => '用户唯一识别号',
                'ulevel' => '用户层级',
                'Out Bank Id' => '银行ID',
                'In Bank Set Id' => '收款帐号ID',
                'billno' => '订单号',
                'user' => '存款人姓名',
                'type' => '支付类型',
                'money' => '存款金额',
                'useraddtime' => '转账时间',
            ];
            $v_error = $h_instance->get_readable_errors();
            $v_error = $h_instance->h_ack_message($v_error);
            $v_error = $h_instance->h_ack_translate($declare_chinese, $v_error);
            $re = [
//                'code'=>$objCode->general_error->code,
                'code' => 10001,
                'errMsg' => $v_error,
            ];
        } else {
            $params = $_POST;
            $params['site_id'] = SITE_ID;
            $params['ip'] = $ip;
            $params['bank_set_id'] = $params['in_bank_set_id'];
            $params['bank_id'] = $params['out_bank_id'];
            $params['card_user'] = $params['user'];
            $params['pay_type'] = $params['type'];
            $params['user_addtime'] = $params['useraddtime'];
            $params['pay_ip'] = $ip;
            $params['user_addtime'] = $params['user_addtime'] ? $params['user_addtime'] : date('Y-m-d H:i:s', time() + 43200);
            unset($params['in_bank_set_id'], $params['out_bank_id'], $params['user'], $params['type'], $params['useraddtime']);
            $re = $params;
            if ($params['money'] <= 0) {
                $re = [
//                'code'=>$objCode->general_error->code,
                    'code' => 10001,
                    'errMsg' => '存款金额必须大于零',
                ];
                break;

            }
            $save_limit_params = [
                'site_id' => SITE_ID,
                'username' => $params['username']
            ];
            $limit = goto_center_api('getSaveLimit', $save_limit_params);
            if (!empty($limit) && $params['money'] < $limit['bank']['limit_min']) {
                $re = [
                    'code' => $objCode->fail_save_less_than_limit->code,
                    'data' => [
                        'bank' => $limit['bank']
                    ],
                    'errMsg' => '金额不能小于银行卡最低限额'
                ];
                break;
            }
            if (!empty($limit) && $params['money'] > $limit['bank']['limit_max']) {
                $re = [
                    'code' => $objCode->fail_save_more_than_limit->code,
                    'data' => [
                        'bank' => $limit['bank']
                    ],
                    'errMsg' => '金额不能大于银行卡最高限额'
                ];
                break;
            }
            $re = goto_center_api('saveBankOrder', json_encode($params));
        }
        break;
    case 'getprize'://获取奖金信息
        $get_input = ['username', 'oid'];
        $params['current'] = time();
        $re = prepare_goto_hprose($get_input, 'getBonusList', $params);
        //####################################################################
        $log_to_write = [
            'start' => "########################################################",
            'goto' => "getBonusList \n ------------------------------------",
            'input' => json_encode($params, JSON_UNESCAPED_UNICODE),
            'result' => json_encode($re, JSON_UNESCAPED_UNICODE),
            'level' => $action
        ];
        $log_write = log_args_write($log_to_write);
        //#####################################################################
        if ($re['code'] == $objCode->get_prizes->code) {
            $re['data']['total'] = 0;
            $datap = json_decode($re['info'], TRUE);
            //$re['data'] = array('prizes' => json_decode($re['info']));
            if ($datap) {
                $etime = time();
                $config = CommonClass::getSiteConfig($clientA);
//                $old_bonus_money_code = 0;
                foreach (array_reverse($datap) as $i => $v) {
                    $datap[$i]['add_time'] = date("Y-m-d H:i:s", $v['add_time']);
                    $datap[$i]['end_time'] = date("Y-m-d H:i:s", $v['end_time']);
                    //start --- imp reverse data
                    $datap[$i]['id'] = $v['id'];
                    $datap[$i]['username'] = $v['username'];
                    $datap[$i]['bonus_money'] = $v['bonus_money'];
                    $datap[$i]['bonus_money_code'] = $v['bonus_money_code'];
                    $datap[$i]['status'] = $v['status'];
                    $datap[$i]['marks'] = $v['marks'];
                    //end --- imp reverse data
                    if ($v['status'] < 2) {
                        $str = SITE_ID . $v['username'] . date("Y-m-d", $v['add_time']) . date("Y-m-d", $etime) . date("H:i:s", $v['add_time']) . date("H:i:s", $etime);
                        $data = [
                            'siteId' => SITE_ID,
                            'username' => $v['username'],
                            'betTimeBegin' => date("Y-m-d", $v['add_time']),
                            'betTimeEnd' => date("Y-m-d", $etime),
                            'startTime' => date("H:i:s", $v['add_time']),
                            'endTime' => date("H:i:s", $etime),
                            'key' => CommonClass::get_key_param($str, 5, 6)
                        ];
                        $result1 = goto_coffee_mix('auditTotalTemp', MY_TCP_CENTER_HOST, json_encode($data));//小温 前台报表接口
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
                        //####################################################################
                        $log_to_write = [
                            'goto' => "Swoole auditTotalTemp\n ------------------------------------",
                            'constant' => MY_TCP_CENTER_HOST,
                            'input' => json_encode($data, JSON_UNESCAPED_UNICODE),
                            'value' => json_encode($v, JSON_UNESCAPED_UNICODE),
                            'result1' => $result1,
                            'result2' => $result2,
                            'level' => $action
                        ];
                        $log_write = log_args_write($log_to_write);
                        //####################################################################
                        $result = json_decode($result2, TRUE);
                        if ($result['returnCode'] == '900000') {
                            $get_bonus_use_code_param = [
                                'site_id' => SITE_ID,
                                'username' => $v['username'],
                                'get_prize_time' => $v['add_time'],
                                'end_time' => $v['end_time']
                            ];
                            $usecodes = goto_center_api('getBonusUsecodes', $get_bonus_use_code_param);//统计已使用的打码量
                            $old_bonus_money_code = goto_center_api('getBonusActivatedcodes', $get_bonus_use_code_param);//统计已使
                            $usecodes = is_array($usecodes) ? 0 : $usecodes;
                            $old_bonus_money_code = is_array($old_bonus_money_code) ? 0 : $old_bonus_money_code;
                            //####################################################################
                            $log_to_write = [
                                'goto' => "getBonusUsecodes\n ------------------------------------",
                                'input' => json_encode($get_bonus_use_code_param, JSON_UNESCAPED_UNICODE),
                                'bonus_money_code' => $v['bonus_money_code'],
                                'sum_money' => $result['dataList']['totalValidamount'],
                                'usecodes' => is_array($usecodes) ? json_encode($usecodes, JSON_UNESCAPED_UNICODE) : $usecodes,
                                'lot_count' => ($result['dataList']['totalValidamount'] - $usecodes),
                                'old_bonus_code' => is_array($old_bonus_money_code) ? json_encode($old_bonus_money_code, JSON_UNESCAPED_UNICODE) : $old_bonus_money_code,
                                'type' => gettype($usecodes),
                                'level' => $action
                            ];
                            $log_write = log_args_write($log_to_write);
                            //####################################################################
                            $sum_money = isset($result['dataList']['totalValidamount']) ? $result['dataList']['totalValidamount'] : 0; //实际投注，总投注
                            if ($v['bonus_money_code'] <= ($sum_money - $usecodes - $old_bonus_money_code)) {//达到打码量
                                if ($v['status'] == 1) {//从冻结（未激活）转态转为解冻状态（激活）
                                    $change_bonus_status_param = [
                                        'id' => $v['id'],
                                        'status' => 2
                                    ];
                                    $bs = goto_center_api('changeBonusStatus', $change_bonus_status_param);
                                    //####################################################################
                                    $log_to_write = [
                                        'goto' => "changeBonusStatus \n ------------------------------------",
                                        'input' => json_encode($change_bonus_status_param, JSON_UNESCAPED_UNICODE),
                                        'value' => json_encode($v, JSON_UNESCAPED_UNICODE),
                                        'result' => json_encode($bs, JSON_UNESCAPED_UNICODE),
                                        'finish' => "\n ##############################################################",
                                        'level' => $action
                                    ];
                                    $log_write = log_args_write($log_to_write);
                                    //####################################################################
                                    $datap[$i]['status'] = 2;
//                                    $old_bonus_money_code += $v['bonus_money_code'];
                                }
                            }
                        }
                    }
                }
                //统计可变现奖金
                $unget = 0;
                foreach ($datap as $p) {
                    if ($p['status'] < 3) {
                        $unget += $p['bonus_money'];
                    }
                }
                $re['data']['prizes'] = array_reverse($datap);
                $re['data']['total'] = $unget;
            }
        }
        break;
    case 'getprizesout':
        $get_input = ['username', 'oid'];
        $res = prepare_goto_hprose($get_input, 'isLoginUpload', $params);
        //####################################################################
        $log_to_write = [
            'start' => "########################################################",
            'goto' => "isLoginUpload \n ------------------------------------",
            'input' => json_encode($params, JSON_UNESCAPED_UNICODE),
            'result' => json_encode($res, JSON_UNESCAPED_UNICODE),
            'level' => $action
        ];
        $log_write = log_args_write($log_to_write);
        //####################################################################
        if ($res['code'] != $objCode->is_login_status->code) {//用户已经登出，或异常
            unset($res['info']);
            $re = $res;
            break;
        }
        $session = json_decode($res['info'], TRUE);
        $id = CommonClass::filter_input_init(INPUT_POST, 'id', FILTER_SANITIZE_MAGIC_QUOTES);
        $one = goto_center_api('getBonusOne', $id);
        //####################################################################
        $log_to_write = [
            'goto' => "getBonusOne\n ------------------------------------",
            'input' => $id,
            'result' => json_encode($one, JSON_UNESCAPED_UNICODE),
            'level' => $action
        ];
        $log_write = log_args_write($log_to_write);
        //####################################################################
        if ($one['code'] == $objCode->get_prizes->code) {
            $par = json_decode($one['info'], TRUE);
            if ($par['end_time'] < time()) {
                $re = [
                    "code" => $objCode->fail_take_bonus->code,
                    'data' => '奖金过时'
                ];
                break;
            }
            $etime = time();
            $config = CommonClass::getSiteConfig($clientA);
            $str = SITE_ID . $par['username'] . date("Y-m-d", $par['add_time']) . date("Y-m-d", $etime) . date("H:i:s", $par['add_time']) . date("H:i:s", $etime);
            $data = [
                'siteId' => SITE_ID,
                'username' => $par['username'],
                'betTimeBegin' => date("Y-m-d", $par['add_time']),
                'betTimeEnd' => date("Y-m-d", $etime),
                'startTime' => date("H:i:s", $par['add_time']),
                'endTime' => date("H:i:s", $etime),
                'key' => CommonClass::get_key_param($str, 5, 6)
            ];
            $result1 = goto_coffee_mix('auditTotalTemp', MY_TCP_CENTER_HOST, json_encode($data));//小温 前台报表获取打码量接口
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
            //####################################################################
            $log_to_write = [
                'goto' => "Swoole auditTotal\n ------------------------------------",
                'input' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'result1' => $result1,
                'result2' => $result2,
                'level' => $action
            ];
            $log_write = log_args_write($log_to_write);
            //####################################################################
            $result = json_decode($result2, TRUE);
            if ($result['returnCode'] == '900000') {
                $get_bonus_use_code_param = [
                    'site_id' => SITE_ID,
                    'username' => $par['username'],
                    'get_prize_time' => $par['add_time'],
                    'end_time' => $par['end_time']
                ];
                $usecodes = goto_center_api('getBonusUsecodes', $get_bonus_use_code_param);//统计已使用的打码量
                $usecodes = is_array($usecodes) ? 0 : $usecodes;
                //####################################################################
                $log_to_write = [
                    'goto' => "getBonusUsecodes\n ------------------------------------",
                    'input' => json_encode($get_bonus_use_code_param, JSON_UNESCAPED_UNICODE),
                    'usecodes' => is_array($usecodes) ? json_encode($usecodes, JSON_UNESCAPED_UNICODE) : $usecodes,
                    'type' => gettype($usecodes),
                    'level' => $action
                ];
                $log_write = log_args_write($log_to_write);
                //####################################################################
                $sum_money = is_null($result['dataList']['totalValidamount']) ? 0 : $result['dataList']['totalValidamount']; //实际投注，总投注
                $par['can_use_codes'] = $sum_money - $usecodes;
                $par['sum_codes'] = $sum_money;
                //####################################################################
                $log_to_write = [
                    'goto' => "getBonusUsecodes\n ------------------------------------",
                    'sum_codes' => $sum_money,
                    'usecodes' => $usecodes,
                    'can_use_codes' => $par['can_use_codes'],
                    'bonus_money_code' => $par['bonus_money_code'],
                    'level' => $action
                ];
                $log_write = log_args_write($log_to_write);
                //####################################################################
                if ($par['bonus_money_code'] <= ($sum_money - $usecodes)) //达到打码量
                {
                    if (substr($session['user_type'], -1) == 1) {//主副站修改
                        $constant = MY_TCP_MONEY_HOST_TEST;//光光
                    } else {
                        $constant = MY_TCP_MONEY_HOST;//光光
                    }
                    $key = CommonClass::get_key_param(SITE_WEB . $par['username'] . 'prize' . $par['id'], 5, 6);
                    $paramsb = [
                        'fromKey' => SITE_WEB,
                        'siteId' => SITE_ID,
                        'username' => $par['username'],
                        'remitno' => 'prize' . $par['id'],
                        'remit' => $par['bonus_money'],
                        'transType' => 'in',
                        'fromKeyType' => '10047',
                        'key' => $key,
                        'memo' => '会员提出奖金'
                    ];
                    $res = goto_coffee_mix('transMoney', $constant, json_encode($paramsb));
                    //####################################################################
                    $log_to_write = [
                        'goto' => "Swoole transMoney\n ------------------------------------",
                        'constant' => $constant,
                        'input' => json_encode($paramsb, JSON_UNESCAPED_UNICODE),
                        'res' => $res,
                        'level' => $action
                    ];
                    $log_write = log_args_write($log_to_write);
                    //####################################################################
                    $rkresult = json_decode($res, TRUE);
                    $re = $rkresult['code'] != '100000' ? [
                        "code" => $objCode->fail_take_bonus->code,
                        'data' => '存款失败', $rkresult
                    ] : goto_center_api('takeBonusOut', $par['id']);
                    //####################################################################
                    $log_to_write = [
                        'goto' => "after Swoole transMoney\n ------------------------------------",
                        'args' => $par['id'],
                        'res' => json_encode($re, JSON_UNESCAPED_UNICODE),
                        'finish' => '##############################################################',
                        'level' => $action
                    ];
                    $log_write = log_args_write($log_to_write);
                    //####################################################################
                } else {
                    $re = [
                        "code" => 5,
                        'data' => '未达到打码量',
                        'sum_codes' => $sum_money,//总打码量
                        'can_use_codes' => $par['can_use_codes'],//有效打码量
                        'result' => $result,
                        'params' => $data
                    ];
                }
            } else {//失败
                $re = [
                    "code" => $objCode->fail_take_bonus->code,
                    'data' => '打码量查询失败',
                    $result
                ];
            }
        } else {//失败
            $re = [
                "code" => $objCode->fail_take_bonus->code,
                'data' => 'ID错误'
            ];
        }
        break;
    case 'saveuserdetial'://绑定银行卡
        //#################################################################################
        $h_instance = new Harris_Sanitize();
        $_POST = $h_instance->sanitize($_POST);
        $rules = [
            'username' => 'required|alpha_numeric|max_len,12|min_len,3',
            'realname' => 'required|max_len,12|min_len,2',
            'mobile' => 'required|phone_number',
            'oid' => 'required',
            'bank_id' => 'required|numeric|max_len,3|min_len,1',
            'bank_address' => 'required',
            'bank_account' => 'required',
            'bank_password' => 'required|alpha_numeric|max_len,12|min_len,4', ///^[A-Za-z0-9]+$/
        ];
        $filters = [
            'username' => 'trim|sanitize_string',
            'realname' => 'trim',
            'mobile' => 'trim',
            'oid' => 'trim',
            'bank_id' => 'trim',
            'bank_address' => 'trim',
            'bank_account' => 'trim',
            'bank_password' => 'trim',
        ];
        $_POST = $h_instance->filter($_POST, $filters);
        $validated = $h_instance->validate($_POST, $rules);
        if ($validated !== true) {
            $declare_chinese = [
                'username' => '用户名',
                'realname' => '用户真实姓名',
                'mobile' => '手机号码',
                'oid' => '用户唯一识别码',
                'Bank Id' => '银行id',
                'Bank Address' => '银行归属地',
                'Bank Account' => '银行账号',
                'Bank Password' => '银行取款密码',
            ];
            $v_error = $h_instance->get_readable_errors();
            $v_error = $h_instance->h_ack_message($v_error);
            $v_error = $h_instance->h_ack_translate($declare_chinese, $v_error);
            $re = [
                'code' => $objCode->fail_connect_bank->code,
                'data' => 'params error',
                'errMsg' => $v_error,
            ];
        } else {
            $params = $_POST;
            $params['site_id'] = SITE_ID;
            $re = goto_center_api('connectBankAccount', $params);
        }
        break;
    //#################################################################################
    case 'saveuserphone'://绑定手机号
        $get_input = ['username', 'oid', 'mobile'];
        $re = check_param($get_input, $params);
        if ($re !== true) {
            return $re;
        }
        if (!CommonClass::check_mobile_str($params['mobile'])) {
            $re = [
                'code' => $objCode->fail_connect_bank->code,
                'data' => 'not enigth2',
                'errMsg' => '请输入正确的手机号码'
            ];
            break;
        }
        $re = goto_center_api('userphone', $params);
        break;
    case 'getsaveonlinebanks'://获取线上存款供选择银行列表
        $get_input = ['username', 'oid', 'bank_code'];
        $neglegible = ['bank_code'];
        $re = prepare_goto_hprose($get_input, 'getSaveOnlineConfig', $params, $neglegible);
        if ($re['code'] == $objCode->success_get_saveonline_banks->code) {
            $banks = json_decode($re['info']);
            unset($re['info']);
            $re['data'] = ['banks' => $banks];
        }
        break;
    case 'submitsaveonline'://线上存款提交订单
        session_start();
        $params['site_id'] = SITE_ID;
        $params['username'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['ulevel'] = CommonClass::filter_input_init(INPUT_POST, 'ulevel', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['money'] = CommonClass::filter_input_init(INPUT_POST, 'money', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['bank_code'] = CommonClass::filter_input_init(INPUT_POST, 'bank_id', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['vcode'] = CommonClass::filter_input_init(INPUT_POST, 'vcode', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['add_time'] = time();
        $params['user_ip'] = $ip;
        if ($_SESSION['vcode_session'] != $params['vcode'] || empty($params['vcode'])) {//验证码错误
            $re['code'] = $objCode->error_vcode->code;
            break;
        }
        unset($_SESSION['vcode_session']);
        session_destroy();
        //信息不全，提交订单失败
        if (
            empty($params['bank_code']) ||
            empty($params['money']) ||
            empty($params['username']) ||
            empty($params['oid']) ||
            empty($params['ulevel']) ||
            !CommonClass::check_money($params['money']) ||
            $params['money'] <= 0
        ) {
            $re = [
                'code' => $objCode->fail_save_online->code
            ];
            break;
        }
        $limit_params = [
            'site_id' => SITE_ID,
            'ulevel' => $params['ulevel'],
            'username' => $params['username']
        ];
        $limit = goto_center_api('getSaveLimit', $limit_params);
        if (!empty($limit) && $params['money'] < $limit['online']['limit_min']) {
            $re = [
                'code' => $objCode->fail_save_less_than_limit->code,
                'data' => [
                    'online' => $limit['online']
                ]
            ];
            break;
        }
        if (!empty($limit) && $params['money'] > $limit['online']['limit_max']) {
            $re = [
                'code' => $objCode->fail_save_more_than_limit->code,
                'data' => [
                    'online' => $limit['online']
                ]
            ];
            break;
        }
        $re = goto_center_api('saveOnLineOrder', $params);
        if ($re['code'] == $objCode->success_save_online->code) {//在线存款底单提交成功
            $re['data'] = ['gurl' => $re['info']];
        }
        break;
    case 'dsgame':
        $gt = CommonClass::filter_input_init(INPUT_POST, 'gt', FILTER_SANITIZE_MAGIC_QUOTES);
        $pt = CommonClass::filter_input_init(INPUT_POST, 'pt', FILTER_SANITIZE_MAGIC_QUOTES);
        $pt = empty($pt) ? 1 : $pt;
        $re = goto_center_with_paras('getDSGame', $gt, $pt);
        if ($re) {
            $re = json_decode($re, TRUE);
            $re['code'] = isset($re['total']) ? 1 : 2;
        } else {
            $re['code'] = 3;
        }
        break;

    /***图片站部分*********************************************/
    /**
     * @SWG\Post(
     *   path="/action.php?action=getlogo",
     *   tags={"图片站"},
     *   summary="获取logo",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response="302012",
     *     description="获取图片站信息成功"
     *   ),
     *   @SWG\Response(
     *     response="312013",
     *     description="获取图片站信息失败"
     *   )
     *  )
     */
    case 'getlogo'://获取logo
        $re = goto_center_with_paras('imgSearch', IMG_SITE_ID, 'logo');
        if ($re['code'] == $objCode->success_get_img_con->code) {//获取图片站信息成功
            $re['data'] = json_decode($re['info']);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getrotate",
     *   tags={"图片站"},
     *   summary="获取轮播图",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response="302012",
     *     description="获取图片站信息成功"
     *   ),
     *   @SWG\Response(
     *     response="312013",
     *     description="获取图片站信息失败"
     *   )
     *  )
     */
    case 'getrotate'://获取轮播图
        //imgSearch($sid, $classify, $tag = '')
        $re = goto_center_with_paras('imgSearch', IMG_SITE_ID, 'rotate');
        if ($re['code'] == $objCode->success_get_img_con->code) {//获取图片站信息成功
            $re['data'] = json_decode($re['info']);
            unset($re['info']);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getpromotion",
     *   tags={"图片站"},
     *   summary="获取优惠活动信息",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response="302012",
     *     description="获取图片站信息成功"
     *   ),
     *   @SWG\Response(
     *     response="312013",
     *     description="获取图片站信息失败"
     *   )
     *  )
     */
    case 'getpromotion'://获取优惠活动信息
        $re = goto_center_with_paras('imgSearch', IMG_SITE_ID, 'promotion');
        if ($re['code'] == $objCode->success_get_img_con->code) {//获取图片站信息成功
            $re['data'] = json_decode($re['info']);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getnewstag",
     *   tags={"图片站"},
     *   summary="获取关于我们等文案所有标题",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response="302012",
     *     description="获取图片站信息成功"
     *   ),
     *   @SWG\Response(
     *     response="312013",
     *     description="获取图片站信息失败"
     *   )
     *  )
     */
    case 'getnewstag'://获取关于我们等文案所有标题
        $re = goto_center_with_paras('imgSearch', IMG_SITE_ID, 'newstag');
        if ($re['code'] == $objCode->success_get_img_con->code) {//获取图片站信息成功
            $re['data'] = json_decode($re['info']);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getnews",
     *   tags={"图片站"},
     *   summary="获取关于我们等文案",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response="302012",
     *     description="获取图片站信息成功"
     *   ),
     *   @SWG\Response(
     *     response="312013",
     *     description="获取图片站信息失败"
     *   )
     *  )
     */
    case 'getnews'://获取关于我们等文案
        $tag = CommonClass::filter_input_init(INPUT_POST, 'tag', FILTER_SANITIZE_MAGIC_QUOTES);
        if (!$tag) {
            $tag = CommonClass::filter_input_init(INPUT_GET, 'tag', FILTER_SANITIZE_MAGIC_QUOTES);
        }
        $re = goto_center_with_paras('imgSearch', IMG_SITE_ID, 'news', $tag);
        if ($re['code'] == $objCode->success_get_img_con->code) {//获取图片站信息成功
            $re['data'] = json_decode($re['info']);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getpopnotice",
     *   tags={"图片站"},
     *   summary="获取弹跳公告",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response="302012",
     *     description="获取图片站信息成功"
     *   ),
     *   @SWG\Response(
     *     response="312013",
     *     description="获取图片站信息失败"
     *   )
     *  )
     */
    case 'getpopnotice'://获取弹跳公告
        $re = goto_center_with_paras('imgSearch', IMG_SITE_ID, 'notice');
        if ($re['code'] == $objCode->success_get_img_con->code) {//获取图片站信息成功
            $re['data'] = json_decode($re['info']);
        }
        break;
    /***限额部分*********************************************/
    /**
     * @SWG\Post(
     *   path="/action.php?action=getlimittypeone",
     *   tags={"公共信息"},
     *   summary="获取限额平台",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response="302014",
     *     description="获取限额信息成功"
     *   ),
     *   @SWG\Response(
     *     response="312015",
     *     description="获取限额信息失败"
     *   )
     *  )
     */
    case 'getlimittypeone'://获取限额平台
        $re = goto_center_api('getLimitTypeOne');
        if ($re['code'] == $objCode->success_get_limit->code) {
            $re['data'] = json_decode($re['info']);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getlimittypetwo",
     *   tags={"公共信息"},
     *   summary="获取限额大类",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="type_one_id",
     *     in="formData",
     *     description="限额平台id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="302014",
     *     description="获取限额信息成功"
     *   ),
     *   @SWG\Response(
     *     response="312015",
     *     description="获取限额信息失败"
     *   )
     *  )
     */
    case 'getlimittypetwo'://获取限额大类
        $type_one_id = CommonClass::filter_input_init(INPUT_POST, 'type_one_id', FILTER_SANITIZE_MAGIC_QUOTES);
        $re = goto_center_api('getLimitTypeTwo', $type_one_id);
        if ($re['code'] == $objCode->success_get_limit->code) {
            $re['data'] = json_decode($re['info']);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getlimitdata",
     *   tags={"公共信息"},
     *   summary="获取限额信息",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="type_one_id",
     *     in="formData",
     *     description="type_two_id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="302014",
     *     description="获取限额信息成功"
     *   ),
     *   @SWG\Response(
     *     response="312015",
     *     description="获取限额信息失败"
     *   )
     *  )
     */
    case 'getlimitdata'://获取限额信息
        $type_two_id = CommonClass::filter_input_init(INPUT_POST, 'type_two_id', FILTER_SANITIZE_MAGIC_QUOTES);
        $re = goto_center_with_paras('getLimitData', $type_two_id, SITE_ID);
        if ($re['code'] == $objCode->success_get_limit->code) {
            $re['data'] = json_decode($re['info']);
        }
        break;
    /***完善资料部分*********************************************/
    case 'getuserdetail':
        $params['username'] = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['site_id'] = SITE_ID;
        $re = goto_center_api('getUserDetails', $params);
        $re['data'] = json_decode($re['info'], TRUE);
        break;
    case 'updateuserdetail':
        $get_input = [
            'username',
            'oid',
            'zip_code',
            'register_address',
            'email',
            'qq',
            'date_of_birth',
            'bank_account',
            'get_password',
            'realname',
            'nickname'
        ];
        $neglegible = [
            'zip_code',
            'register_address',
            'email',
            'qq',
            'date_of_birth',
            'bank_account',
            'get_password',
            'realname',
            'nickname'
        ];
        $re = check_param($get_input, $params, $neglegible);
        if ($re !== true) {
            break;
        }
        if (!CommonClass::check_username_str($params['username'], 4, 12)) {
            $re = [
                'code' => $objCode->fail_to_reg->code,
                'errMsg' => '用户名必须为4-12位之间的字母和数字'
            ];
            break;
        }
        if ($params['email']) {
            if (!preg_match("/^([a-z0-9A-Z]+[-|\\.]?)+[a-z0-9A-Z]@([a-z0-9A-Z]+(-[a-z0-9A-Z]+)?\\.)+[a-zA-Z]{2,}$/", $params['email'])) {
                $re = [
                    'code' => $objCode->errMsgApp->code,
                    'errMsg' => '请输入正确的电子邮箱'
                ];
                break;
            }
            $re = [
                'email' => $params['email'],
                'company' => SITE_ID,
                'ip' => $ip
            ];
            $re = goto_center_api('checkEmail', $re);
            if ($re['code'] == $objCode->is_have_email->code) {
                $re = [
                    'code' => $objCode->fail_to_reg->code,
                    'errMsg' => '该电子邮箱已经存在'
                ];
                break;
            }
        }
        if ($params['qq']) {
            if (!preg_match("/^[1-9][0-9]{4,11}$/", $params['qq'])) {
                $re = [
                    'code' => $objCode->fail_to_agentreg->code,
                    'errMsg' => '请输入正确的qq号'
                ];
                break;
            }
            $re = [
                'qq' => $params['qq'],
                'company' => SITE_ID,
                'ip' => $ip
            ];
            $re = goto_center_api('checkQq', $re);
            if ($re['code'] == $objCode->is_have_qq->code) {
                $re = [
                    'code' => $objCode->fail_to_reg->code,
                    'errMsg' => '该qq已经存在'
                ];
                break;
            }
        }
        if (empty($params['email']) && empty($params['qq'])) {
            $re = [
                'code' => $objCode->fail_to_reg->code,
                'errMsg' => '电子邮箱和QQ号至少填写一个'
            ];
            break;
        }
        if (!empty($params['nickname']) && !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]{2,8}$/u", $params['nickname'])) {
            $re = [
                'code' => $objCode->fail_to_reg->code,
                'errMsg' => '昵称必须是2-8位的中文、字母和数字'
            ];
            break;
        }
        $re = goto_center_api('updateUserDetails', $params);
        $log_to_write = [
            'input' => json_encode($params, JSON_UNESCAPED_UNICODE),
            'goto' => 'updateUserDetails',
            'result' => json_encode($re, JSON_UNESCAPED_UNICODE),
            'level' => $action
        ];
        $log_write = log_args_write($log_to_write);
        //$re['data'] = json_decode($re['info'],TRUE);
//username:tiehao
//oid:eb147f7d14fa60ffbee04193d2a6bd06
//zip_code:527227
//register_address:菲律宾KG技术
//email:126@126.com
//qq:7895642
//date_of_birth:1969-12-31
//bank_account:62235623223
//get_password:123456
        break;
    //推广红利部分
    /**
     * @SWG\Post(
     *   path="/action.php?action=getspreadpromos",
     *   tags={"用户信息"},
     *   summary="查询推广红利",
     *   description="返回结果中：<br>allpage：总页数",
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
     *     name="page",
     *     in="formData",
     *     description="第几页",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="count",
     *     in="formData",
     *     description="每页条数",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="start_time",
     *     in="formData",
     *     description="开始时间",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="end_time",
     *     in="formData",
     *     description="结束时间",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="103054",
     *     description="获取推广红利成功"
     *   ),
     *   @SWG\Response(
     *     response="113055",
     *     description="获取推广红利失败"
     *   )
     *  )
     */
    case 'getspreadpromos':
        $params['username'] = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['site_id'] = SITE_ID;
        $params['page'] = CommonClass::filter_input_init(INPUT_POST, 'page', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['page'] = !empty($params['page']) && $params['page'] > 1 ? $params['page'] : 1;
        $params['count'] = CommonClass::filter_input_init(INPUT_POST, 'count', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['count'] = !empty($params['count']) && $params['count'] > 1 ? $params['count'] : 8;
        $params['start'] = ($params['page'] - 1) * $params['count'];
        $params['start_time'] = CommonClass::filter_input_init(INPUT_POST, 'start_time', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['end_time'] = CommonClass::filter_input_init(INPUT_POST, 'end_time', FILTER_SANITIZE_MAGIC_QUOTES);
        if (empty($params['start_time'])) {
            $params['start_time'] = 0;
        } else {
            $params['start_time'] = strtotime($params['start_time']);
        }
        if (empty($params['end_time'])) {
            $params['end_time'] = time();
        } else {
            $params['end_time'] = strtotime($params['end_time']);
        }
        $re = goto_center_api('getSpreadPromos', $params);
        if ($re['code'] == $objCode->success_get_spread_promos->code) {
            $re['data'] = json_decode($re['info'], TRUE);
        }
        $config = CommonClass::getSiteConfig($clientA);
        $re['appurl'] = APPURL;
        break;
    /**
     * 用户签到接口
     * author: harris
     */
    case 'calendarCheckin':
        $get_input = ['username', 'oid'];
        $re = prepare_goto_hprose($get_input, 'calender_sign', $params);
        if (isset($re['info'])) {
            $re['data'] = json_decode($re['info']);
            unset($re['info']);
        }
        $re['code'] = $re['code'] ? $re['code'] : '1001';
        if ($re['code'] != $objCode->all_success->code) {
            $log_to_write = [
                '用户名' => $params['username'],
                '签到' => '失败',
                '参数' => json_encode($_REQUEST),
                '返回得到结果' => json_encode($re),
                'level' => $action
            ];
            $log_write = log_args_write($log_to_write);
            if ($log_write !== true) {
                $re['log'] = $log_write;
            }
        } else {
            $log_to_write = [
                '用户名' => $params['username'],
                '签到' => '成功',
                '参数' => json_encode($_REQUEST),
                '返回得到结果' => json_encode($re),
                'level' => $action
            ];
            $log_write = log_args_write($log_to_write);
            if ($log_write !== true) {
                $re['log'] = $log_write;
            }
        }
        break;
    /**
     * 用户签到获取记录接口
     * author: harris
     */
    case 'calendarGetRecord':
        $get_input = ['username', 'oid'];
        $re = prepare_goto_hprose($get_input, 'calender_sign_get_records', $params);
        if (isset($re['info'])) {
            $re['data'] = json_decode($re['info']);
            unset($re['info']);
        }
        $re['code'] = $re['code'] ? $re['code'] : '1001';
        if ($re['code'] != $objCode->all_success->code) {
            $log_to_write = [
                '用户名' => $params['username'],
                '获取签到记录' => '失败',
                '参数' => json_encode($_REQUEST),
                '返回得到结果' => json_encode($re),
                'level' => $action
            ];
            $log_write = log_args_write($log_to_write);
            if ($log_write !== true) {
                $re['log'] = $log_write;
            }
        } else {
            $log_to_write = [
                '用户名' => $params['username'],
                '获取签到记录' => '成功',
                '参数' => json_encode($_REQUEST),
                '返回得到结果' => json_encode($re),
                'level' => $action
            ];
            $log_write = log_args_write($log_to_write);
            if ($log_write !== true) {
                $re['log'] = $log_write;
            }
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getServerInfo",
     *   tags={"公共信息"},
     *   summary="获取服务器信息",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
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
    case 'getServerInfo'://获取服务器信息
        $re = [
            'code' => '1000',
            'data' => [
                'time' => time()
            ]
        ];
        break;
    /*
     * 获取房间
     * author: harris
     */
    case 'getroomstables':
        $code = strtolower(CommonClass::filter_input_init(INPUT_POST, 'code', FILTER_SANITIZE_MAGIC_QUOTES));
        $params['code'] = empty($code) ? SITE_ID : $code;
        $re = goto_center_api('getroomstables', $params);
        break;
    /*
     * 获取赔率
     * author: harris
     */
    case 'getgameodds':
        $get_input = ['tableId'];
        $re = prepare_goto_hprose($get_input, 'get_compensation_rate', $params);
        break;

    case 'get_game_current_bet':
        $h_instance = new Harris_Sanitize();
        $_POST = $h_instance->sanitize($_POST);
        $rules = [
            'username' => 'required|alpha_numeric|max_len,12|min_len,3',
            'pageNumber' => 'required|numeric|max_len,3|min_len,1',
            'pageSize' => 'required|numeric|max_len,3|min_len,1',
        ];
        $filters = [
            'username' => 'trim|sanitize_string',
            'pageNumber' => 'trim|intval',
            'pageSize' => 'trim|intval',
            'gameType' => 'trim',
        ];
        $_POST = $h_instance->filter($_POST, $filters);
        $validated = $h_instance->validate($_POST, $rules);
        if ($validated !== true) {
            $declare_chinese = [
                'username' => '用户名',
                'pageNumber' => '页码',
                'pageSize' => '页面大小码',
                'gameType' => '用户类型',
            ];
            $v_error = $h_instance->get_readable_errors();
            $v_error = $h_instance->h_ack_message($v_error);
            $v_error = $h_instance->h_ack_translate($declare_chinese, $v_error);
            $re = [
//                'code'=>$objCode->general_error->code,
                'code' => 10001,
                'errMsg' => $v_error,
            ];
        } else {
            $get_arr = [
                'username' => $_POST['username'],
                'pageNumber' => $_POST['pageNumber'],
                'pageSize' => $_POST['pageSize'],
                'siteId' => SITE_ID,
                'gameType' => $_POST['gameType'],
            ];//主副站修改
            $re = goto_center_api('get_game_current_bet', $get_arr);
        }
        break;
    /*
     * 获取摇奖初始化数据
     * author: harris
     */
    case 'shakeInit':
        $get_input = ['username', 'oid'];
        $st = prepare_goto_hprose($get_input, 'ernieLogin', $params);
        if (array_key_exists("errMsg", $st)) {
            $re = $st;
            break;
        } else if ($st['code'] == 1000) {
            $re['score'] = $st['data']['u_points']; //返回积分
            $re['code'] = 10000;
            $re['username'] = $params['username'];
            $params['get_column'] = ['id', 'amount'];//to get the result of column
            $re['play'] = goto_center_api('get_draw_probability', $params);
            /*go to user update shake data */
            $chk_shake_user = goto_center_api('check_shake_user', $params);
            if (!isset($chk_shake_user['error'])) {
                $params['sql_data'] = $chk_shake_user;
                if ($params['oid'] != $params['sql_data']['uuid']) {
                    $update_shake_user = goto_center_api('update_shake_user', $params);
                }
            } else {
                $add_shake_user = goto_center_api('add_shake_user', $params);
            }
            if ($re['code'] != 10000) {
                $status = isset($update_shake_user) ? $update_shake_user : $add_shake_user;
                $re['errMsg'] =
                    /*'errMsg' => $mess = (!is_array($status)) ? $status : '无需更新',*/
                $mess = (!is_array($status)) ? '状态更新' : $sh = $status['errMsg'] . ' 原因= ' . $status['error'];
            }
        } else {
            $re = $st;
        }
        break;
    /*
     * 获取摇奖结果
     * author: harris
     */
    case 'shakeResult':
        $get_input = ['username', 'oid'];
        $shake_raw = [
            'SCORE_DEL' => 5, //一次摇奖减去多少积分
            'PLAY_TIMES' => 30, //每天最多摇奖次数
        ];
        $chk_shake_user = prepare_goto_hprose($get_input, 'check_shake_user', $params);
        if (array_key_exists("errMsg", $chk_shake_user)) {
            $re = $chk_shake_user;
            break;
        } else if (array_key_exists("error", $chk_shake_user)) {
            $error['content'] = $re['errMsg'] = '用户数据异常';
            $error['status'] = 2003;
            $re['code'] = 89;
            $error['username'] = $re['username'] = $params['username'];
            $error['occur_time'] = $re['occur_time'] = time();
            $re['description'] = $chk_shake_user;
            $draw_error = goto_center_api('add_shake_error', $error);
            if (is_array($draw_error) && array_key_exists("errMsg", $draw_error)) {
                $re = $draw_error;
                $re['errMsg'] = $re['errMsg'] . ' come_from api @ line' . __LINE__;
            }
            break;
        }
        $re = $chk_shake_user;
        //prepare for going to get_draw_probability
        $params['get_column'] = ['id', 'remain_num'];
        $result_probability = goto_center_api('get_draw_probability', $params);
        if (!array_key_exists("error", $result_probability)) {
            $re = [];
            $proArr = [];
            foreach ($result_probability as $key => $value) {
                if ($result_probability[$key]['remain_num'] < 1) {
                    //剩余奖品小于1
                    $error_remain_num_list = isset($re['error_remain_num_list']) ? $re['error_remain_num_list'] . "," . $result_probability[$key]['id'] : $result_probability[$key]['id'];
                    $re['error_remain_num_list'] = $error_remain_num_list;
//                    $re['error_status'] = 2004;
//                    $re['username'] = $params['username'];
//                    $re['occur_time'] = time();
//                    $re['errMsg'] = '抽奖数据异常，请联系客服';
//                    $re['code'] = 911;
                    continue;
                }
                $proArr[$result_probability[$key]['id']] = $result_probability[$key]['remain_num'];
            }
            if (array_key_exists("error_remain_num_list", $re)) {
                $error['content'] = $re['error_remain_num_list'] . '抽奖数据异常';
                $error['status'] = 2004;
                $error['username'] = $params['username'];
                $error['occur_time'] = time();
                $draw_error = goto_center_api('add_shake_error', $error);
                if (is_array($draw_error) && array_key_exists("errMsg", $draw_error)) {
                    $re = $draw_error;
                    $re['errMsg'] = $re['errMsg'] . ' come_from api @ line' . __LINE__;
                    break;
                }
            }
            $result = '';
            $proSum = array_sum($proArr);
            if ($proSum < 1) {
                $re = [];
                $error['content'] = $re['errMsg'] = '奖品数量不足';
                $error['status'] = 2006;
                $re['code'] = 966;
                $error['username'] = $re['username'] = $params['username'];
                $error['occur_time'] = $re['occur_time'] = time();
                $draw_error = goto_center_api('add_shake_error', $error);
                if (is_array($draw_error) && array_key_exists("errMsg", $draw_error)) {
                    $re = $draw_error;
                    $re['errMsg'] = $re['errMsg'] . ' come_from api @ line' . __LINE__;
                }
                break;
            }
            //概率数组循环
            /*foreach ($proArr as $key => $proCur) {
                $randNum = mt_rand(1, $proSum); //抽取随机数
                if ($randNum <= $proCur) {
                    $result = $key; //得出结果
                    break;
                } else {
                    //不放回
                    $proSum -= $proCur; //只有-=、+=
                }
            }*/
            $arr_formula = prob_formula_create($proArr, $possible_out_comes);
            $result = drawing($arr_formula, $possible_out_comes);
//            $result = random_result_draw_probability($proArr);
            unset($proArr);
            /*$data['id'] = $result;*/
            $params['u_points'] = $shake_raw['SCORE_DEL'];
            $params['type'] = 'LOTTERY_COST_POINT';//摇奖减积分
            $resScore = goto_center_api('ernieUpdatePoints', $params);
            if ($resScore['code'] == 1000) {
                $re = [];
                $actual_score = $re['score'] = $resScore['info']['u_points']; //返回积分
                //写摇奖日志开始
                $params['result_num'] = $result;
                $params['SCORE_DEL'] = $shake_raw['SCORE_DEL'];
                $draw_history = goto_center_api('add_shake_history', $params);
                if (is_array($draw_history) && array_key_exists("errMsg", $draw_history)) {
                    $re = $draw_history;
                    $re['errMsg'] = $re['errMsg'] . ' come_from api @ line' . __LINE__;
                    break;
                }
                unset($params['ernieUpdatePoints_result'], $params['SCORE_DEL']);
                //写摇奖日志结束
            } else if ($resScore['code'] == 1002) {//积分不足
                $re = [];
                $re['errMsg'] = '摇奖失败：用户积分不足';//积分不足，请充值
                $re['code'] = 96;
                break;
            } else {
                $re = [];
                $error['status'] = 2002;
                $re['code'] = 989;
                $error['username'] = $re['username'] = $params['username'];
                $error['occur_time'] = $re['occur_time'] = time();
                $error['content'] = $re['errMsg'] = '积分扣分返回异常，请联系客服';
                //写摇奖错误日志开始
                $draw_error = goto_center_api('add_shake_error', $error);
                if (is_array($draw_error) && array_key_exists("errMsg", $draw_error)) {
                    $re = $draw_error;
                    $re['errMsg'] = $re['errMsg'] . ' come_from api @ line' . __LINE__;
                }
                //写摇奖错误日志结束
                break;
            }
            //prepare for going to transaction_pb
            $params['data_id'] = [
                'id' => $result
            ];
            $params['get_column'] = ['amount', 'remain_num', 'mt_poroms'];
            $result_probability_transaction = goto_center_api('transaction_pb', $params);
            if ($result_probability_transaction == 'rb') {
                $re = [];
                $re['resultNum'] = 1; //奖金为0的主键ID，用于异常时返回
                $re['score'] = $actual_score;
                $re['resultName'] = '0.00';
                $re['code'] = 100;
                break;
            } else if (array_key_exists("error", $result_probability_transaction)) {
                $re = $result_probability_transaction;
                $re['score'] = $actual_score;
                break;
            } else {
                /*
                 * if needed use array_push here
                 */
                $re = $result_probability_transaction;
                /*$re['score'] = $result_probability;
                */
                $result_amount = $re['result_select']['amount'];
                $result_amount_count = $re['result_select']['mt_poroms'];
                $result_amount = is_null($result_amount) ? '0.00' : $result_amount;

                //3，派奖接口,大于0则派奖
                if ($result_amount > 0) {
                    $prizeData['username'] = $params['username'];
                    $prizeData['bonus_money'] = $result_amount;
                    $prizeData['bonus_money_code'] = $result_amount_count;
                    $prizeData['site_id'] = $params['site_id'];
                    $addPrize = goto_center_api('ernieAddBonus', $prizeData);
                    /*if ($addPrize['error'] == 1000) {
                        return 1;
                    }*/
                    /*if ($addPrize < 0)*/
                    //派奖异常
                    if ($addPrize['error'] != 1000) {
                        $error['content'] = $re['errMsg'] = '派奖异常' . date("Y-m-d H:i:s", time());
                        $error['status'] = 2001;
                        $re['code'] = 100;
                        $error['username'] = $re['username'] = $params['username'];
                        $error['occur_time'] = $re['occur_time'] = time();
                        //写摇奖错误日志开始
                        $draw_error = goto_center_api('add_shake_error', $error);
                        if (is_array($draw_error) && array_key_exists("errMsg", $draw_error)) {
                            $re = $draw_error;
                            $re['errMsg'] = $re['errMsg'] . ' come_from api @ line' . __LINE__;
                        }
                        //写摇奖错误日志结束
                        break;
                    }
                }

                //4,返回结果
                $re['resultNum'] = $result;
                $re['score'] = $actual_score;
                $re['resultName'] = $result_amount;
                $re['code'] = 10000;
                if (array_key_exists("result_select", $re)) {
                    unset($re['result_select']);
                }
            }
        }//db no error case;
        else {
            $re = $result_probability;
        }
        break;
    case 'test':
        $code = strtolower(CommonClass::filter_input_init(INPUT_POST, 'code', FILTER_SANITIZE_MAGIC_QUOTES));
        $params['code'] = empty($code) ? SITE_ID : $code;
        $re = goto_center_api('test', $params);
        break;
    case 'health':
        $re = ['code' => 'I am ok'];
        break;
    /*
     * 获取以后的开封盘计划
     * author: harris
     */
    case 'getopenclose':
        $get_input = ['gameType', 'termCount'];
        $re = prepare_goto_hprose($get_input, 'get_open_close', $params);
        break;
    /*
     * 获取微信链接
     * author: harris
     */
    case 'wechat_QR':
        $re = thirdparty_online_pay('weixin');
        if ($re['code'] == 10000) {
            $re = gotcurl($re['data']['url']);
            if (is_object($re)) {
                $result = json_encode($re);
                $result = json_decode($result, true);
                $re = [];
                if (isset($result['url'])) {
                    $re['code'] = $result['code'];
                    $re['data'] = [
                        'url' => $result['url']
                    ];
                } else if (isset($result['errMsg'])) {
                    $re = $result;
                }
            } else {
                $re['code'] = '00001';
                $re['errMsg'] = '系统异常，请稍后重试';
            }
        }
        break;
    /*
     * 获取支付宝链接
     * author: harris
     */
    case 'alipay_QR':
        $re = thirdparty_online_pay('ZHIFUBAO');
        if ($re['code'] == 10000) {
            $re = gotcurl($re['data']['url']);
            if (is_object($re)) {
                $result = json_encode($re);
                $result = json_decode($result, true);
                $re = [];
                if (isset($result['url'])) {
                    $re['code'] = $result['code'];
                    $re['data'] = [
                        'url' => $result['url']
                    ];
                } else if (isset($result['errMsg'])) {
                    $re = $result;
                }
            } else if (empty($re)) {
                $re = [];
                $re['code'] = '00000';
                $re['errMsg'] = '支付未返回请重试';
            } else {
                $re['code'] = '00001';
                $re['errMsg'] = '系统异常，请稍后重试';
            }
        }
        break;
    /*
     * 获取网银链接
     * author: harris
     */
    case 'thirdparty_online_pay':
        $re = thirdparty_online_pay('third_banks');
        break;
    /*
     * 代理层级同步链接
     * author: harris
     */
    case 'agent_level_synchronization':
        $re = goto_center_api('agent_level_synchronization');
        break;
    /*
     * 用户登陆缓存
     * author: harris
     */
    case 'rd_all':
        $h_instance = new Harris_Sanitize();
        $_POST = $h_instance->sanitize($_POST);
        $rules = [
            'db' => 'required|integer',
        ];
        $filters = [
            'db' => 'trim|intval',
            'getkey' => 'trim|intval'
        ];
        $_POST = $h_instance->filter($_POST, $filters);
        $validated = $h_instance->validate($_POST, $rules);
        if ($validated !== true) {
            $declare_chinese = [
                'db' => '库',
            ];
            $v_error = $h_instance->get_readable_errors();
            $v_error = $h_instance->h_ack_message($v_error);
            $v_error = $h_instance->h_ack_translate($declare_chinese, $v_error);
            $re['errMsg'] = $v_error;
        } else {
            $db = $_POST['db'];
            $getkey = $_POST['getkey'];
            $re = goto_center_with_paras('get_agent_level_hash', $db, $getkey);
        }
        break;
    case 'rd_child_all':
        $del = $_POST['action'];
        $rd = new CommonClass();
        $connection = $rd->redisconnect(REDIS_DB);
        $rk = $connection->keys('*');
        $data = [];
        foreach ($rk as $key => $value) {
            $json_str = $connection->get($value);
            $json_arr = json_decode($json_str, true);
            if ($del == 2) {
                $connection->del($value);
            } else if (!is_array($json_arr) && is_null($json_arr)) {
                $data[$value] = $json_str;
            } else {
                if (isset($json_arr['info'])) unset($json_arr['info']);
                $rd_value = json_encode($json_arr, JSON_UNESCAPED_UNICODE);
                if ((empty($rd_value) || $rd_value == 'null') && $del == 1) {
                    $connection->del($value);
                } else {
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
                    ], $rd_value);
                    $data[$value] = $result;
                }
            }
        }
        $data = array_filter($data);
        if (empty($data)) {
            $re['message'] = 'there is no pair value';
        } else {
            $re = $data;
        }
        break;
    case 'rd_agent_clear':
        $re = goto_center_api('clear_agent_level_hash');
        break;
    case 'clearLog':
        $day = $_REQUEST['day'];
        $api = $_REQUEST['api'];
        if ($api == 1) {
            try {
                $re = goto_center_api('del_expire_log', $day);
            } catch (Exception $e) {
                $re = $e->getTraceAsString();
            }
        } else {
            $re['status'] = del_expire_log($day);
        }
        break;
    case 'harris_sanitize':
        $h_instance = new Harris_Sanitize();
        $_POST = $h_instance->sanitize($_POST);
        $rules = [
            'username' => 'required|alpha_numeric|max_len,100|min_len,6',
            'password' => 'required|max_len,100|min_len,6',
            'email' => 'required|valid_email',
            'gender' => 'required|max_len,6',
            'mobile' => 'required|phone_number',
            'money' => 'required|float',
        ];
        $filters = [
            'username' => 'trim|sanitize_string',
            'password' => 'trim|md5',//base64_encode
            'email' => 'trim|sanitize_email',
            'gender' => 'trim',
            'money' => 'trim',
        ];
        $_POST = $h_instance->filter($_POST, $filters);
        $validated = $h_instance->validate($_POST, $rules);
        $_POST['money'] = number_format((float)$_POST['money'], 2, '.', '');
        if ($validated !== true) {
            $declare_chinese = [
                'gender' => '性别',
                'mobile' => '手机号码'
            ];
            $v_error = $h_instance->get_readable_errors();
            $v_error = $h_instance->h_ack_message($v_error);
            $v_error = $h_instance->h_ack_translate($declare_chinese, $v_error);
            $re['code'] = $objCode->paramerror1->code;
            $re['errMsg'] = $v_error;
        } else {
            $re = $_POST;
        }
        break;
    /*
     * 后台调用会员存取款刷新接口
     * author: harris
     */
    case 'stop_refresh_deposit':
        $key = 'dssession_8000_' . $_REQUEST['username'];
        $status = $_REQUEST['status'];
        $re = goto_center_with_paras('stop_refresh_deposit', $key, $status);
        break;
    /*
     * 02.获取客服配置信息 (因为存在跨域问题 改到我 这边来了）
     * author: harris
     */
//    case 'custom_service_setting':
//        $url = "http://static.ds88online.com/APP/kefu_hx/config_lanbo.json?v=1487574073909";
//        $re = gotcurl($url);
//        $re = json_encode($re, JSON_UNESCAPED_UNICODE);
//        echo $re;
//        die();
//        break;
    case 'test_goto_center':
        $re = goto_center_with_paras('test_goto_center', 'aa', 'cc', 'dd');
        break;
    case 'siteid':
        $re['site_id'] = SITE_ID;
        $re['SITE_TYPE'] = SITE_TYPE;
        $re['IMG_SITE_ID'] = IMG_SITE_ID;
        break;
    default:
        session_start();
        $re = [
            'code' => 10001,
            'errMsg' => $_SERVER['REQUEST_URI'] . '接口不存在'
        ];
        break;
}
/*if (isset($re['info'])) {
    unset($re['info']);
}*/
echo CommonClass::ajax_return($re, $jsonp, $jsonpcallback);
?>
