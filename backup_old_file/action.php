<?php
header('Access-Control-Allow-Origin:*');
ini_set("display_errors", "on");
require_once("config.php");
switch ($action) {
    /**
     * @SWG\Post(
     *   path="/action.php?action=register",
     *   tags={"用户信息"},
     *   summary="用户注册",
     *   description="注册<br>需要进行异步验证用户信息（参见用户注册异步验证接口）<br>网站关闭注册时：返回fail_to_reg_not_notallow：1 (1允许，2主站不允许，3副站不允许注册，4主副站不允许注册",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名(4-12个小写字母或数字的组合)",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="密码(8-12个英文字母或数字)",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="agree",
     *     in="formData",
     *     description="1：同意注册条约 0：不同意(agree 需要前端自己验证)",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="intr",
     *     in="formData",
     *     description="上级代理",
     *     required=false,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="sp",
     *     in="formData",
     *     description="介绍人",
     *     required=false,
     *     type="string",
     *   ),
     *   @SWG\Response(
     *     response="201018",
     *     description="用户注册并且登录成功！(登录需要session保存用户信息)"
     *   ),
     *   @SWG\Response(
     *     response="201019",
     *     description="用户注册成功，请返回登录！"
     *   ),
     *   @SWG\Response(
     *     response="211020",
     *     description="用户注册失败，请稍后再试！"
     *   ),
     *   @SWG\Response(
     *     response="211035",
     *     description="注册失败，同一IP注册账号过多！"
     *   )
     * )
     */
    case 'register'://会员注册
        $config = CommonClass::getSiteConfig($clientA);
        if (CAN_REGISTER == 4 || (CAN_REGISTER == 2 && SITE_TYPE == 1) || (CAN_REGISTER == 3 && SITE_TYPE == 2)) {
            $re = array('fail_to_reg_not_notallow' => $objCode->fail_to_reg->code, 'errMsg' => '网站类型错误');
            break;
        }
        $username = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
//        $truename = CommonClass::filter_input_init(INPUT_POST, 'realname',FILTER_SANITIZE_MAGIC_QUOTES);
//        $mobile = CommonClass::filter_input_init(INPUT_POST, 'mobile',FILTER_SANITIZE_MAGIC_QUOTES);
        $mobile = null;
        $truename = '';
        $password = CommonClass::filter_input_init(INPUT_POST, 'password', FILTER_SANITIZE_MAGIC_QUOTES);
//        $passwd = CommonClass::filter_input_init(INPUT_POST, 'passwd',FILTER_SANITIZE_MAGIC_QUOTES);
        $agree = CommonClass::filter_input_init(INPUT_POST, 'agree', FILTER_SANITIZE_MAGIC_QUOTES);
        $intr = CommonClass::filter_input_init(INPUT_POST, 'intr', FILTER_SANITIZE_MAGIC_QUOTES);
        $sp = CommonClass::filter_input_init(INPUT_POST, 'sp', FILTER_SANITIZE_MAGIC_QUOTES);
//        $email = CommonClass::filter_input_init(INPUT_POST, 'email',FILTER_SANITIZE_MAGIC_QUOTES);
//        $qq = CommonClass::filter_input_init(INPUT_POST, 'qq',FILTER_SANITIZE_MAGIC_QUOTES);
        $unionid = strtolower(trim(CommonClass::filter_input_init(INPUT_POST, 'unionid', FILTER_SANITIZE_MAGIC_QUOTES)));
        $nickname = strtolower(trim(CommonClass::filter_input_init(INPUT_POST, 'nickname', FILTER_SANITIZE_MAGIC_QUOTES)));//昵称
        if (!empty($nickname) && !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]{2,8}$/u", $nickname)) {
            $re = array('code' => $objCode->fail_to_reg->code, 'errMsg' => '昵称必须是2-8位的中文、字母和数字');
            break;
        }
        if (!empty($unionid)) {
            $username = $unionid;
            $password = $unionid . $loginkeyb;
        } else {
            if (!CommonClass::check_username_str($username, 4, 12)) {
                $re = array('code' => $objCode->fail_to_reg->code, 'errMsg' => '用户名必须为4-12位之间的字母和数字');
                break;
            }
            if (!CommonClass::check_password_str($password, 8, 12)) {
                $re = array('code' => $objCode->fail_to_reg->code, 'errMsg' => '登录密码必须为8-12位之间的字母和数字');
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
            $re = array('code' => $objCode->fail_to_reg->code, 'errMsg' => '用户名或者密码不能为空');
            break;
        }
        //验证用户名存在
        $data1 = array('username' => $username, 'company' => SITE_ID, 'ip' => $ip);
        $re1 = $clientA->checkUser($data1);
        if ($re1['code'] == $objCode->is_have_username->code) {
            $re = array('code' => $objCode->fail_to_reg->code, 'errMsg' => '用户名已存在');
            break;
        }
        //验证真实姓名存在
//        $data2 = array('realname' => $truename, 'company' => SITE_ID, 'ip' => $ip) ;
//        $re2 = $clientA->checkTrueName($data2);
//        if ($re2['code'] == $objCode->is_have_truename->code) {
//            $re = array('code' => $objCode->fail_to_reg->code);
//            break;
//        }
        //验证手机号码存在
//        $data3 = array('mobile' => $mobile, 'company' => SITE_ID, 'ip' => $ip);
//        $re3 = $clientA->checkMobile($data3);
//        if ($re3['code'] == $objCode->is_have_mobile->code) {
//            $re = array('code' => $objCode->fail_to_reg->code);
//            break;
//        }
        $pwd = CommonClass::get_md5_pwd($password);
        $params = array(
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
        );
        if (!empty($intr)) {
            $params['agid'] = $intr;
        }
        if (!empty($sp)) {
            $params['sp'] = $sp;
        }
        if (!empty($email)) {
            if (!preg_match("/^([a-z0-9A-Z]+[-|\\.]?)+[a-z0-9A-Z]@([a-z0-9A-Z]+(-[a-z0-9A-Z]+)?\\.)+[a-zA-Z]{2,}$/", $email)) {
                $re = array('code' => $objCode->errMsgApp->code, 'errMsg' => '请输入正确的电子邮箱');
                break;
            }
            $params['email'] = $email;
        }
        if (!empty($qq)) {
            if (!preg_match("/^[1-9][0-9]{4,11}$/", $qq)) {
                $re = array('code' => $objCode->fail_to_agentreg->code, 'errMsg' => '请输入正确的qq号');
                break;
            }
            $params['qq'] = $qq;
        }
        $params['host'] = $host;
        $re = $clientA->userRegister($params);
        if ($re['code'] == $objCode->success_to_reg_and_login->code) {
            $re['data'] = json_decode($re['info'], TRUE);
            $re['data']['last_login_time'] = date("y-m-d H:i:s");
            $re['data']['supers'] = $re['data']['super'];
            $v_k = 'VIP' . $re['data']['uvip'];
            $re['data']['uvipname'] = $config['api_config'][$v_k];
            unset($re['data']['super']);
            $pa['siteId'] = SITE_ID;
            $pa['username'] = $username;
            $pa['agents'] = $re['data']['agent'];
            $pa['world'] = $re['data']['world'];
            $pa['corprator'] = $re['data']['corprator'];
            $pa['superior'] = $re['data']['supers'];
            $pa['company'] = 'admin';
            $demo_user = array('1' => '11', '2' => '21');
            $site_type = SITE_TYPE;//主副站修改
            if ($re['data']['user_type'] == $demo_user[$site_type]) {//主副站修改
//                $clientF = new HproseSwooleClient(MY_TCP_MONEY_HOST_TEST); //光光
            } else {
//                $clientF = new HproseSwooleClient(MY_TCP_MONEY_HOST); //光光临时注释
                $clientD = new HproseSwooleClient(MY_TCP_MEMBER_HOST); //光光临时注释
                $x = $clientD->setMemberData(json_encode($pa));
            }
//            $y = $clientF->setMemberData(json_encode($pa));
//            echo "<pre>";
//            print_r($pa);
//            print_r($x);
//            echo "</pre>";
//            $re['moneycenter'] = json_decode($x, TRUE);
//            $re['baobiao'] = json_decode($y, TRUE);
        } else {
            $re['data'] = json_decode($re['info'], TRUE);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=agentregister",
     *   tags={"用户信息"},
     *   summary="代理注册",
     *   description="注册<br>需要进行异步验证用户信息（参见用户注册异步验证接口）<br>返回结果中的tips：(1参数错误，2用户名已被使用，3真实姓名被使用，4手机号被使用 5注册成功",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名(4-12个小写字母或数字的组合)",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="密码(8-12个英文字母或数字)",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="passwd",
     *     in="formData",
     *     description="重复密码(8-12个英文字母或数字)",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="realname",
     *     in="formData",
     *     description="真实姓名",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="mobile",
     *     in="formData",
     *     description="手机号",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     description="邮箱",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="qq",
     *     in="formData",
     *     description="qq",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="bankname",
     *     in="formData",
     *     description="银行名称",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="bankaccount",
     *     in="formData",
     *     description="银行卡号",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="bankaddress",
     *     in="formData",
     *     description="银行归属地",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="agree",
     *     in="formData",
     *     description="1：同意条款 0：不同意条款",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="211021",
     *     description="代理注册失败，请稍后再试"
     *   ),
     *   @SWG\Response(
     *     response="201022",
     *     description="代理注册成功"
     *   ),
     * )
     */
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
            $re = array('code' => $objCode->fail_to_agentreg->code, 'errMsg' => '请输入正确的qq号');
            break;
        }
        if (empty($username)) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '用户名不能为空');
            break;
        }
        if (empty($truename)) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '真实姓名必须填写');
            break;
        }
        if (empty($mobile)) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '手机号码必须填写');
            break;
        }
        if (
        empty($password)
        ) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '密码必须填写');
            break;
        }
        if (
        empty($passwd)
        ) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '第二次密码不能为空！');
            break;
        }
        if (
        empty($bankname)
        ) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '银行名称必须选择');
            break;
        }
        if (
        empty($bankaccount)
        ) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '银行账号必须填写');
            break;
        }
        if (
            $password != $passwd
        ) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '请输入相同的两次密码！');
            break;
        }
        if (
        !CommonClass::check_agentname_str($username, 4, 12)
        ) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '用户名必须为4-12位之间的字母和数字');
            break;
        }
        if (
        !CommonClass::check_password_str($password, 8, 12)
        ) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '登录密码必须为8-12位之间的字母和数字');
            break;
        }
        if (
        !CommonClass::check_mobile_str($mobile)
        ) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'tips' => 1, 'errMsg' => '请填写正确的手机号码');
            break;
        }
        //验证用户名存在
        $data1 = array('username' => $username, 'company' => SITE_ID, 'ip' => $ip);
        $re1 = $clientA->checkAgentUser($data1);
        if ($re1['code'] == $objCode->is_have_username->code) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'tips' => 2, 'errMsg' => '用户名已经存在');
            break;
        }
        //验证真实姓名存在
        $data2 = array('realname' => $truename, 'company' => SITE_ID, 'ip' => $ip);
        $re2 = $clientA->checkAgentTrueName($data2);
        if ($re2['code'] == $objCode->is_have_truename->code) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'tips' => 3, 'errMsg' => '真实姓名已经存在');
            break;
        }
        //验证手机号码存在
        $data3 = array('mobile' => $mobile, 'company' => SITE_ID, 'ip' => $ip);
        $re3 = $clientA->checkAgentMobile($data3);
        if ($re3['code'] == $objCode->is_have_mobile->code) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'tips' => 4, 'errMsg' => '手机号码已经存在');
            break;
        }
        //后期银行卡验证 //TODO
        $pwd = CommonClass::get_md5_pwd($password);
        $params = array(
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
        );
        if (!empty($email)) {
            if (!preg_match("/^([a-z0-9A-Z]+[-|\\.]?)+[a-z0-9A-Z]@([a-z0-9A-Z]+(-[a-z0-9A-Z]+)?\\.)+[a-zA-Z]{2,}$/", $email)) {
                $re = array('code' => $objCode->fail_to_agentreg->code, 'errMsg' => '请输入正确的电子邮箱');
                break;
            }
            $params['email'] = $email;
        }
        if (!empty($qq)) {
            $params['qq'] = $qq;
        }
        $re = $clientA->agentRegister(json_encode($params));
        $re['tips'] = 5;
        $pa['siteId'] = SITE_ID;
        $pa['username'] = $re['Hierarchy']['username'];
        $pa['agents'] = $re['Hierarchy']['agents'];
        $pa['world'] = $re['Hierarchy']['world'];
        $pa['corprator'] = $re['Hierarchy']['corprator'];
        $pa['superior'] = $re['Hierarchy']['superior'];
        $pa['company'] = 'admin';
        $demo_agent = array('1' => '10', '2' => '20');
        $site_type = SITE_TYPE; //主副站修改
        if ($re['Hierarchy']['user_type'] == $demo_agent[$site_type]) {//主副站修改
            $config = CommonClass::getSiteConfig($clientA);
            $clientD = new HproseSwooleClient(MY_TCP_MONEY_HOST); //光光
            $x = $clientD->setMemberData(json_encode($pa));
        }
        unset($re['Hierarchy']);
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=checkreg",
     *   tags={"用户信息"},
     *   summary="用户注册异步验证",
     *   description="验证时：一次只验证一项<br>可验证用户名，真实姓名，手机号，邮箱，qq，银行账号",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="ausername",
     *     in="formData",
     *     description="用户名",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="arealname",
     *     in="formData",
     *     description="真实姓名",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="amobile",
     *     in="formData",
     *     description="手机号",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="aemail",
     *     in="formData",
     *     description="邮箱",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="aqq",
     *     in="formData",
     *     description="qq",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="abankaccount",
     *     in="formData",
     *     description="银行卡号",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="目前使用的用户(只在验证银行卡号的时候需要传)",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="211004",
     *     description="该用户名已经存在"
     *   ),
     *   @SWG\Response(
     *     response="201005",
     *     description="该用户名可以使用"
     *   ),
     *   @SWG\Response(
     *     response="211006",
     *     description="该真实姓名已经存在"
     *   ),
     *   @SWG\Response(
     *     response="201007",
     *     description="该真实姓名可以使用"
     *   ),
     *   @SWG\Response(
     *     response="211008",
     *     description="该手机号码已经存在"
     *   ),
     *   @SWG\Response(
     *     response="201009",
     *     description="该手机号码可以使用"
     *   ),
     *   @SWG\Response(
     *     response="211040",
     *     description="该电子邮箱已经存在"
     *   ),
     *   @SWG\Response(
     *     response="201041",
     *     description="该电子邮箱可以使用"
     *   ),
     *   @SWG\Response(
     *     response="211042",
     *     description="该QQ已经存在"
     *   ),
     *   @SWG\Response(
     *     response="201043",
     *     description="该QQ可以使用"
     *   ),
     *   @SWG\Response(
     *     response="211044",
     *     description="该银行账号已经存在"
     *   ),
     *   @SWG\Response(
     *     response="201045",
     *     description="该银行账号可以使用"
     *   )
     *  )
     */
    case 'checkreg'://会员注册异步校验
        $username = strtolower(CommonClass::filter_input_init(INPUT_POST, 'ausername', FILTER_SANITIZE_MAGIC_QUOTES));
        $truename = CommonClass::filter_input_init(INPUT_POST, 'arealname', FILTER_SANITIZE_MAGIC_QUOTES);
        $mobile = CommonClass::filter_input_init(INPUT_POST, 'amobile', FILTER_SANITIZE_MAGIC_QUOTES);
        $email = CommonClass::filter_input_init(INPUT_POST, 'aemail', FILTER_SANITIZE_MAGIC_QUOTES);
        $qq = CommonClass::filter_input_init(INPUT_POST, 'aqq', FILTER_SANITIZE_MAGIC_QUOTES);
        if (!empty($qq) && !preg_match("/^[1-9][0-9]{4,11}$/", $qq)) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'errMsg' => '请输入正确的qq号');
            break;
        }
        if (!empty($email) && !preg_match("/^([a-z0-9A-Z]+[-|\\.]?)+[a-z0-9A-Z]@([a-z0-9A-Z]+(-[a-z0-9A-Z]+)?\\.)+[a-zA-Z]{2,}$/", $email)) {
            $re = array('code' => $objCode->errMsgApp->code, 'errMsg' => '请输入正确的电子邮箱');
            break;
        }
        $bankaccount = CommonClass::filter_input_init(INPUT_POST, 'abankaccount', FILTER_SANITIZE_MAGIC_QUOTES);
        $nowusername = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        if ($username) {
            $params = array('username' => $username, 'company' => SITE_ID, 'ip' => $ip);
            $re = $clientA->checkUser($params);
        } else if ($truename) {
            $params = array('realname' => $truename, 'company' => SITE_ID, 'ip' => $ip);
            $re = $clientA->checkTrueName($params);
        } else if ($mobile) {
            $params = array('mobile' => $mobile, 'company' => SITE_ID, 'ip' => $ip);
            $re = $clientA->checkMobile($params);
        } else if ($email) {
            $params = array('email' => $email, 'company' => SITE_ID, 'ip' => $ip);
            $re = $clientA->checkEmail($params);
        } else if ($qq) {
            $params = array('qq' => $qq, 'company' => SITE_ID, 'ip' => $ip);
            $re = $clientA->checkQq($params);
        } else if ($bankaccount) {
//            $params = array('bankaccount' => $bankaccount, 'company' => SITE_ID, 'ip' => $ip);
            $params = array('bankaccount' => $bankaccount, 'company' => SITE_ID, 'ip' => $ip, 'username' => $nowusername);
            $re = $clientA->checkBankaccount($params);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=checkagentreg",
     *   tags={"用户信息"},
     *   summary="代理注册异步验证",
     *   description="验证时：一次只验证一项<br>可验证用户名，真实姓名，手机号，邮箱，qq，银行账号",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="ausername",
     *     in="formData",
     *     description="用户名",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="arealname",
     *     in="formData",
     *     description="真实姓名",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="amobile",
     *     in="formData",
     *     description="手机号",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="aemail",
     *     in="formData",
     *     description="邮箱",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="aqq",
     *     in="formData",
     *     description="qq",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="abankaccount",
     *     in="formData",
     *     description="银行卡号",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="目前使用的用户(只在验证银行卡号的时候需要传)",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="211004",
     *     description="该用户名已经存在"
     *   ),
     *   @SWG\Response(
     *     response="201005",
     *     description="该用户名可以使用"
     *   ),
     *   @SWG\Response(
     *     response="211006",
     *     description="该真实姓名已经存在"
     *   ),
     *   @SWG\Response(
     *     response="201007",
     *     description="该真实姓名可以使用"
     *   ),
     *   @SWG\Response(
     *     response="211008",
     *     description="该手机号码已经存在"
     *   ),
     *   @SWG\Response(
     *     response="201009",
     *     description="该手机号码可以使用"
     *   ),
     *   @SWG\Response(
     *     response="211040",
     *     description="该电子邮箱已经存在"
     *   ),
     *   @SWG\Response(
     *     response="201041",
     *     description="该电子邮箱可以使用"
     *   ),
     *   @SWG\Response(
     *     response="211042",
     *     description="该QQ已经存在"
     *   ),
     *   @SWG\Response(
     *     response="201043",
     *     description="该QQ可以使用"
     *   ),
     *   @SWG\Response(
     *     response="211044",
     *     description="该银行账号已经存在"
     *   ),
     *   @SWG\Response(
     *     response="201045",
     *     description="该银行账号可以使用"
     *   )
     *  )
     */
    case 'checkagentreg'://代理注册异步校验
        $username = strtolower(CommonClass::filter_input_init(INPUT_POST, 'ausername', FILTER_SANITIZE_MAGIC_QUOTES));
        if (!empty($username)) {
            $username = 'd' . $username;
        }
        $truename = CommonClass::filter_input_init(INPUT_POST, 'arealname', FILTER_SANITIZE_MAGIC_QUOTES);
        $mobile = CommonClass::filter_input_init(INPUT_POST, 'amobile', FILTER_SANITIZE_MAGIC_QUOTES);
        $email = CommonClass::filter_input_init(INPUT_POST, 'aemail', FILTER_SANITIZE_MAGIC_QUOTES);
        if (!empty($email) && !preg_match("/^([a-z0-9A-Z]+[-|\\.]?)+[a-z0-9A-Z]@([a-z0-9A-Z]+(-[a-z0-9A-Z]+)?\\.)+[a-zA-Z]{2,}$/", $email)) {
            $re = array('code' => $objCode->errMsgApp->code, 'errMsg' => '请输入正确的电子邮箱');
            break;
        }
        $qq = CommonClass::filter_input_init(INPUT_POST, 'aqq', FILTER_SANITIZE_MAGIC_QUOTES);
        if (!empty($qq) && !preg_match("/^[1-9][0-9]{4,11}$/", $qq)) {
            $re = array('code' => $objCode->fail_to_agentreg->code, 'errMsg' => '请输入正确的qq号');
            break;
        }
        $bankaccount = CommonClass::filter_input_init(INPUT_POST, 'abankaccount', FILTER_SANITIZE_MAGIC_QUOTES);
        if ($username) {
            $params = array('username' => $username, 'company' => SITE_ID, 'ip' => $ip);
            $re = $clientA->checkAgentUser($params);
        } else if ($truename) {
            $params = array('realname' => $truename, 'company' => SITE_ID, 'ip' => $ip);
            $re = $clientA->checkAgentTrueName($params);
        } else if ($mobile) {
            $params = array('mobile' => $mobile, 'company' => SITE_ID, 'ip' => $ip);
            $re = $clientA->checkAgentMobile($params);
        } else if ($email) {
            $params = array('email' => $email, 'company' => SITE_ID, 'ip' => $ip);
            $re = $clientA->checkAgentEmail($params);
        } else if ($qq) {
            $params = array('qq' => $qq, 'company' => SITE_ID, 'ip' => $ip);
            $re = $clientA->checkAgentQq($params);
        } else if ($bankaccount) {
            $params = array('bankaccount' => $bankaccount, 'company' => SITE_ID, 'ip' => $ip);
            $re = $clientA->checkAgentBankaccount($params);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=checpwd",
     *   tags={"用户信息"},
     *   summary="会员修改密码验证旧密码",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="ypassword",
     *     in="formData",
     *     description="原密码",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="201023",
     *     description="原密码正确"
     *   ),
     *   @SWG\Response(
     *     response="211024",
     *     description="原密码不正确，请重新输入"
     *   )
     * )
     */
    case 'checpwd'://会员修改密码验证旧密码
        $username = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        $ypassword = CommonClass::filter_input_init(INPUT_POST, 'ypassword', FILTER_SANITIZE_MAGIC_QUOTES);
        $password = CommonClass::get_md5_pwd($ypassword);
        $params = array('username' => $username, 'company' => SITE_ID, 'password' => $password, 'ip' => $ip);
        $re = $clientA->checkPassword($params);
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=checpwdget",
     *   tags={"用户信息"},
     *   summary="会员修改出款密码验证旧密码",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="ypassword",
     *     in="formData",
     *     description="原密码",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="201023",
     *     description="原密码正确"
     *   ),
     *   @SWG\Response(
     *     response="211024",
     *     description="原密码不正确，请重新输入"
     *   )
     * )
     */
    case 'checpwdget'://会员修改出款密码验证旧密码
        $username = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        $ypassword = CommonClass::filter_input_init(INPUT_POST, 'ypassword', FILTER_SANITIZE_MAGIC_QUOTES);
        $password = CommonClass::get_md5_pwd($ypassword);
        $params = array('username' => $username, 'company' => SITE_ID, 'password' => $password, 'ip' => $ip);
        $re = $clientA->checkPasswordGet($params);
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=login",
     *   tags={"用户信息"},
     *   summary="登录接口",
     *   description="描述",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="密码",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="211002",
     *     description="登录失败,请稍后重试"
     *   ),
     *   @SWG\Response(
     *     response="211003",
     *     description="登录失败！"
     *   ),
     *   @SWG\Response(
     *     response="201001",
     *     description="登录成功！"
     *   ),
     *   @SWG\Response(
     *     response="211029",
     *     description="该用户已被停用"
     *   ),
     *   @SWG\Response(
     *     response="211030",
     *     description="验证码错误"
     *   ),
     *   @SWG\Response(
     *     response="201001",
     *     description="登录成功！"
     *   )
     * )
     */
    case 'login'://登录
        $username = strtolower(trim(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES)));
        $password = trim(CommonClass::filter_input_init(INPUT_POST, 'password', FILTER_SANITIZE_MAGIC_QUOTES));
        $unionid = strtolower(trim(CommonClass::filter_input_init(INPUT_POST, 'unionid', FILTER_SANITIZE_MAGIC_QUOTES)));
        if (!empty($unionid)) {
            $username = $unionid;
            $password = $unionid . $loginkeyb;
        }
        if (empty($username)) {
            $re['code'] = $objCode->fail_to_login_error->code;
            break;
        }
        if (empty($password)) {
            $re['code'] = $objCode->fail_to_login_error->code;
            break;
        }
        $config = CommonClass::getSiteConfig($clientA);
        if ($username == -1) {//试玩
            $re = $clientA->testPlayLogin(array('company' => SITE_ID, 'ip' => $ip, 'user_type' => SITE_TYPE));//主副站修改
        } else {
            $pwd = CommonClass::get_md5_pwd($password);
            $vcode = trim(CommonClass::filter_input_init(INPUT_POST, 'varcode', FILTER_SANITIZE_MAGIC_QUOTES));
            if (NEED_CODE == 1) {//需要验证码
                session_start();
                if ($vcode != (isset($_SESSION['vcode_session']) ? $_SESSION['vcode_session'] : null) || empty($vcode)) {
                    $re['code'] = $objCode->fail_to_login_error_code->code;
                    break;
                }
            }
            $params = array('username' => $username, 'company' => SITE_ID, 'userpass' => $pwd, 'ip' => $ip, 'user_type' => SITE_TYPE);//主副站修改
            $re = $clientA->login($params);
        }
        if ($re['code'] == $objCode->success_to_login->code) {
            CommonClass::myLog($username . '登录');
            include_once 'clientGetObj.php';
            $os = new clientGetObj();
            $par['site_id'] = SITE_ID;
            $par['username'] = $username;
            $par['os'] = $os->get_os();
            $par['browse'] = $os->get_broswer();
            $par['ip'] = $ip;
            $par['addtime'] = time();
            $clientA->recordClient($par);
            $re['data'] = json_decode($re['info'], TRUE);
            $re['data']['last_login_time'] = date("y-m-d H:i:s");
            $re['data']['supers'] = $re['data']['super'];
            $v_k = 'VIP' . $re['data']['uvip'];
            $re['data']['uvipname'] = $config['api_config'][$v_k];
            unset($re['data']['super']);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getnotice",
     *   tags={"公共信息"},
     *   summary="公告",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="note_type",
     *     in="formData",
     *     description="1：普通公告  2：弹窗公告  98:会员中心所有公告 副站 99:会员中心所有公告 主站",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="302001",
     *     description="获取公告成功！"
     *   ),
     *   @SWG\Response(
     *     response="312002",
     *     description="获取公告失败！"
     *   )
     * )
     */
    case 'getnotice'://获取公告信息
        $params['company'] = SITE_ID;
        //$params['note_type'] = CommonClass::get_notice_type(, $type);
        $params['note_type'] = CommonClass::filter_input_init(INPUT_POST, 'note_type', FILTER_SANITIZE_MAGIC_QUOTES);
        $re = $clientA->getNotice($params);
        if ($re['code'] == $objCode->success_get_notice->code) {
            $re['data'] = array('notice' => json_decode($re['info']));
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getnoticehistory",
     *   tags={"公共信息"},
     *   summary="获取历史公告信息",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="note_type",
     *     in="formData",
     *     description="98:会员中心所有公告 副站 99:会员中心所有公告 主站",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="302003",
     *     description="获取历史公告成功！"
     *   ),
     *   @SWG\Response(
     *     response="312004",
     *     description="获取历史公告失败！"
     *   )
     * )
     */
    case 'getnoticehistory'://获取历史公告信息
        $params['company'] = SITE_ID;
        $params['note_type'] = CommonClass::filter_input_init(INPUT_POST, 'note_type', FILTER_SANITIZE_MAGIC_QUOTES);
        $re = $clientA->getNoticeHistory($params);
        if ($re['code'] == $objCode->success_get_history_notice->code) {
            $re['data'] = array('notice' => json_decode($re['info']));
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getmessage",
     *   tags={"公共信息"},
     *   summary="获取短信息",
     *   description="返回结果",
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
     *     response="302007",
     *     description="获取短信息成功！"
     *   ),
     *   @SWG\Response(
     *     response="312008",
     *     description="获取短信息失败！"
     *   )
     * )
     */
    case 'getmessage'://获取短信息
        $params['company'] = SITE_ID;
        $params['username'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $re = $clientA->getMessage($params);
        if ($re['code'] == $objCode->success_get_message->code) {
            $re['data'] = array('message' => json_decode($re['info']));
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=setmessage",
     *   tags={"公共信息"},
     *   summary="修改短信息为已读",
     *   description="返回结果",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="formData",
     *     description="消息id",
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
     *     response="113057",
     *     description="参数为空"
     *   ),
     *   @SWG\Response(
     *     response="312016",
     *     description="修改短信息成功"
     *   ),
     *   @SWG\Response(
     *     response="312017",
     *     description="修改短信息失败"
     *   )
     * )
     */
    case 'setmessage'://修改短信息
        $params['company'] = SITE_ID;
        $params['username'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $params['id'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'id', FILTER_SANITIZE_MAGIC_QUOTES));
        if (empty($params['username']) || empty($params['id'])) {
            echo CommonClass::ajax_return(array('code' => $objCode->paramerror1->code, 'info' => $objCode->paramerror1->message), $jsonp, $jsonpcallback);
            exit;
        }
        $re = $clientA->setMessage($params);
        if ($re['code'] == $objCode->success_set_message->code) {
            $re['data'] = array('message' => $re['info']);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getcountmessage",
     *   tags={"公共信息"},
     *   summary="获取短信息未读条数",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="1000",
     *     description="获取成功！"
     *   ),
     *   @SWG\Response(
     *     response="1001",
     *     description="获取失败！"
     *   )
     * )
     */
    case 'getcountmessage'://获取短信息未读条数
        $params['company'] = SITE_ID;
        $params['username'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $re['count'] = $clientA->getCountMessage($params);
        $re['code'] = '1000';
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=logout",
     *   tags={"用户信息"},
     *   summary="退出登录",
     *   description="返回结果",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Response(
     *     response="201013",
     *     description="登录状态"
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     * )
     */
    case 'logout'://登出
        $username = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $params = array('username' => $username, 'company' => SITE_ID, 'ip' => $ip);
        $re = $clientA->logout($params);
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getbanks",
     *   tags={"公共信息"},
     *   summary="获取银行列表",
     *   description="返回结果",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response="103009",
     *     description="获取所有银行信息失败"
     *   ),
     *   @SWG\Response(
     *     response="103011",
     *     description="获取奖金列表数据"
     *   ),
     * )
     */
    case "getbanks"://获取银行列表
        $re = $clientA->getAllBankInfo();
        if ($re['code'] == $objCode->success_get_banks->code) {
            $re['data'] = array('banks' => json_decode($re['info']));
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=protect",
     *   tags={"公共信息"},
     *   summary="获取维护信息",
     *   description="返回结果<br>status(1：正常 2：维护)<br>allregister(1:可注册 2：禁止注册)<br>vcode(1:需要验证码 2：不需要)",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="tag",
     *     in="formData",
     *     description="标签",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="aprotect",
     *     in="formData",
     *     description="是否获取所有维护状态 1:是 0 不是",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Response(
     *     response="302005",
     *     description="获取维护信息成功"
     *   ),
     *   @SWG\Response(
     *     response="312006",
     *     description="获取维护信息失败"
     *   ),
     * )
     */
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
        $re = $clientA->getProtectStatus(SITE_ID, $tag, $aprotect);
        $allregister = 1;//允许注册
        if (CAN_REGISTER == 4 || (CAN_REGISTER == 2 && SITE_TYPE == 1) || (CAN_REGISTER == 3 && SITE_TYPE == 2)) {
            $allregister = 2;//关闭注册功能
        }
        if ($re['code'] == $objCode->success_get_weihu->code) {
            $re['data'] = array('protect' => json_decode($re['info']), 'vcode' => NEED_CODE, 'allregister' => $allregister);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=changepassword",
     *   tags={"用户信息"},
     *   summary="修改密码",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="ypassword",
     *     in="formData",
     *     description="原密码",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="新密码",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="oid",
     *     in="formData",
     *     description="oid",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="user_type",
     *     in="formData",
     *     description="主副站",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="201015",
     *     description="修改密码成功"
     *   ),
     *   @SWG\Response(
     *     response="211016",
     *     description="修改密码失败，请联系客服"
     *   ),
     *   @SWG\Response(
     *     response="211017",
     *     description="修改密码失败，原密码错误"
     *   )
     * )
     */
    case 'changepassword'://修改密码
        $username = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $ypassword = CommonClass::filter_input_init(INPUT_POST, 'ypassword', FILTER_SANITIZE_MAGIC_QUOTES);
        if (empty($ypassword)) {
            $re = array('code' => $objCode->fail_to_change_pwd->code, 'errMsg' => '原密码不能为空');
            break;
        }
        $password = CommonClass::filter_input_init(INPUT_POST, 'password', FILTER_SANITIZE_MAGIC_QUOTES);
        if (empty($password)) {
            $re = array('code' => $objCode->fail_to_change_pwd->code, 'errMsg' => '现密码不能为空');
            break;
        }
        $oid = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $user_type = CommonClass::filter_input_init(INPUT_POST, 'user_type', FILTER_SANITIZE_MAGIC_QUOTES);//主副站修改
        if (empty($user_type)) {
            $re = array('code' => $objCode->fail_to_change_pwd->code, 'errMsg' => 'user_type不能为空');
            break;
        }
        $pwd = CommonClass::get_md5_pwd($ypassword);
        $newpwd = CommonClass::get_md5_pwd($password);
        $params = array('username' => $username, 'userpass' => $pwd, 'newuserpass' => $newpwd, 'company' => SITE_ID, 'oid' => $oid, 'ip' => $ip, 'user_type' => $user_type);//主副站修改
        $re = $clientA->updatePassword($params);
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=changegetpassword",
     *   tags={"用户信息"},
     *   summary="修改取款密码",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="ypassword",
     *     in="formData",
     *     description="原密码",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="新密码",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="oid",
     *     in="formData",
     *     description="oid",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="user_type",
     *     in="formData",
     *     description="主副站",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="201015",
     *     description="修改密码成功"
     *   ),
     *   @SWG\Response(
     *     response="211016",
     *     description="修改密码失败，请联系客服"
     *   ),
     *   @SWG\Response(
     *     response="211017",
     *     description="修改密码失败，原密码错误"
     *   )
     * )
     */
    case 'changegetpassword'://修改密码
        $username = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $oid = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $ypassword = CommonClass::filter_input_init(INPUT_POST, 'ypassword', FILTER_SANITIZE_MAGIC_QUOTES);
        if (empty($ypassword)) {
            $re = array('code' => $objCode->fail_to_change_pwd->code, 'errMsg' => '原密码不能为空');
            break;
        }
        /* else{
             $pwd = CommonClass::get_md5_pwd($ypassword);
             $params = array('username' => $username, 'userpass' => $pwd, 'company' => SITE_ID, 'oid' => $oid, 'ip' => $ip,'user_type'=>$user_type);//主副站修改
             $re = $clientA->checkGetPassword($params);
         }*/
        $pwd = CommonClass::get_md5_pwd($ypassword);
        $password = CommonClass::filter_input_init(INPUT_POST, 'password', FILTER_SANITIZE_MAGIC_QUOTES);
        if (empty($password)) {
            $re = array('code' => $objCode->fail_to_change_pwd->code, 'errMsg' => '现密码不能为空');
            break;
        } else if (strlen($password) >= 4 && strlen($password) <= 12) {
            $re = array('code' => $objCode->fail_to_change_pwd->code, 'errMsg' => '请设置现密码为4-12位之间');
            break;
        }
        $newpwd = CommonClass::get_md5_pwd($password);
        $user_type = CommonClass::filter_input_init(INPUT_POST, 'user_type', FILTER_SANITIZE_MAGIC_QUOTES);//主副站修改
        if (empty($user_type)) {
            $re = array('code' => $objCode->fail_to_change_pwd->code, 'errMsg' => 'user_type不能为空');
            break;
        }
        $params = array('username' => $username, 'userpass' => $pwd, 'newuserpass' => $newpwd, 'company' => SITE_ID, 'oid' => $oid, 'ip' => $ip, 'user_type' => $user_type);//主副站修改
        $re = $clientA->updateGetPassword($params);
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getbankset",
     *   tags={"用户信息"},
     *   summary="获取收款银行信息",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="oid",
     *     in="formData",
     *     description="oid",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="103022",
     *     description="获取收款银行信息成功"
     *   ),
     *   @SWG\Response(
     *     response="113023",
     *     description="没有收款银行，请联系客服"
     *   )
     * )
     */
    case 'getbankset'://获取收款银行信息
        $params['username'] = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        //$params['ulevel'] = CommonClass::filter_input_init(INPUT_POST, 'ulevel',FILTER_SANITIZE_MAGIC_QUOTES);
        $params['site_id'] = SITE_ID;
        $re = $clientA->getBanksByUserLevel($params);
        if ($re['code'] == $objCode->success_get_bank_set->code) {
            $re['data'] = array('bankset' => json_decode($re['info']));
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getbillno",
     *   tags={"用户信息"},
     *   summary="获取出款订单",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="oid",
     *     in="formData",
     *     description="oid",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="103024",
     *     description="生成订单号成功"
     *   ),
     *   @SWG\Response(
     *     response="113025",
     *     description="生成订单号失败"
     *   )
     * )
     */
    case 'getbillno'://获取出款订单
        $params['username'] = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['site_id'] = SITE_ID;
        $re = $clientA->getBillno($params);
        if ($re['code'] == $objCode->success_get_billno->code) {
            $re['data'] = array('billno' => $re['info']);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getgetmoneyuserdetail",
     *   tags={"用户信息"},
     *   summary="获取收款银行详细信息",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="用户名",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="oid",
     *     in="formData",
     *     description="oid",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="flag",
     *     in="formData",
     *     description="2为手机必填，3为单独验证手机",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="agent",
     *     in="formData",
     *     description="代理",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="113056",
     *     description="您存款操作已被停用，请联系客服"
     *   ),
     *   @SWG\Response(
     *     response="201025",
     *     description="获取用户出款资料成功"
     *   ),
     *   @SWG\Response(
     *     response="211026",
     *     description="获取用户出款资料失败"
     *   )
     * )
     */
    case 'getgetmoneyuserdetail'://获取收款银行信息
        $params['username'] = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $flag = CommonClass::filter_input_init(INPUT_POST, 'flag', FILTER_SANITIZE_MAGIC_QUOTES);//2为手机必填，3为单独验证手机
        if (empty($flag)) {
            $re = array('code' => $objCode->errMsgApp->code, 'errMsg' => 'flag参数不能为空');
            break;
        }
        $params['site_id'] = SITE_ID;
        $agent = CommonClass::filter_input_init(INPUT_POST, 'agent', FILTER_SANITIZE_MAGIC_QUOTES);
        if (in_array($agent, ['ddm000', 'ddm000k'])) {
            $re = array('code' => $objCode->errMsgApp->code, 'errMsg' => '此代理下的用户没有提款权限');
            break;
        }
        $isAlow = $clientA->isAlowSaveTakeFromAgent(SITE_ID, $agent); //判断允许存取款
        if ($isAlow == 2) {//不允许存取款
            echo CommonClass::ajax_return(array('code' => $objCode->is_not_allow_save->code), $jsonp, $jsonpcallback);
            exit();
        }
        // 判断提款权限
        $kilo['company'] = SITE_ID;
        $kilo['username'] = $params['username'];
        $session = $clientA->getSession($kilo);
        if ($session['code'] == 201018) {
            $res = json_decode($session['info']);
            $money_status = $res->money_status;
            if ($money_status == 2) {
                $re = array('code' => $objCode->errMsgApp->code, 'errMsg' => '用户没有提款权限');
                break;
            }
        } else {
            $re = array('code' => $objCode->errMsgApp->code, 'errMsg' => '用户未登录');
            break;
        }
        if ($flag == 2 || $flag == 3) {
            $re = $clientA->getGetMoneyUserDetail($params, $flag);
        } else {
            $re = $clientA->getGetMoneyUserDetail($params);
        }
//        $re = $clientA->getGetMoneyUserDetail($params);
        $re['data'] = json_decode($re['info']);
        break;
//测试用
    case 'getmemberinfo':
        try {
            $pwd = CommonClass::get_md5_pwd('234567');
            $newpwd = CommonClass::get_md5_pwd('234567');
            $params = array('username' => 'ceshi997', 'userpass' => $pwd, 'newuserpass' => $newpwd, 'company' => 1, 'ip' => $ip);
            $re = $clientA->getMemberInfo($params);
        } catch (Exception $e) {
            print_r($e);
        }
        print_r($re);
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=savebankorder",
     *   tags={"用户信息"},
     *   summary="提交公司存款订单",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="username",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="in_bank_set_id",
     *     in="formData",
     *     description="收款配置id",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="out_bank_id",
     *     in="formData",
     *     description="存款人银行id",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="user",
     *     in="formData",
     *     description="存款人真实姓名",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="type",
     *     in="formData",
     *     description="支付类型(网银转帐 ATM自动柜员机 银行柜台 手机银行转帐)",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="money",
     *     in="formData",
     *     description="存款金额",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="billno",
     *     in="formData",
     *     description="订单号",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="oid",
     *     in="formData",
     *     description="oid",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Parameter(
     *     name="useraddtime",
     *     in="formData",
     *     description="汇款时间",
     *     required=true,
     *     type="string",
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="103007",
     *     description="入款订单成功，请稍后刷新额度"
     *   ),
     *   @SWG\Response(
     *     response="113008",
     *     description="公司入款订单提交失败"
     *   ),
     *   @SWG\Response(
     *     response="113053",
     *     description="存款不能低于最低限额"
     *   ),
     *   @SWG\Response(
     *     response="113052",
     *     description="存款已经超出最高限额"
     *   )
     * )
     */
    case 'savebankorder'://提交公司存款订单
        $ulevel = CommonClass::filter_input_init(INPUT_POST, 'ulevel', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['username'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $params['site_id'] = SITE_ID;
        $params['ip'] = $ip;
        $params['bank_set_id'] = CommonClass::filter_input_init(INPUT_POST, 'in_bank_set_id', FILTER_SANITIZE_MAGIC_QUOTES); //收款人配置id
        $params['bank_id'] = CommonClass::filter_input_init(INPUT_POST, 'out_bank_id', FILTER_SANITIZE_MAGIC_QUOTES);   //存款人银行id
        $params['card_user'] = CommonClass::filter_input_init(INPUT_POST, 'user', FILTER_SANITIZE_MAGIC_QUOTES);        //存款人真实姓名
        $params['pay_type'] = CommonClass::filter_input_init(INPUT_POST, 'type', FILTER_SANITIZE_MAGIC_QUOTES);         //支付类型
        $params['pay_ip'] = $ip;
        $params['money'] = CommonClass::filter_input_init(INPUT_POST, 'money', FILTER_SANITIZE_MAGIC_QUOTES);
        //$params['add_time'] = strtotime(CommonClass::filter_input_init(INPUT_POST, 'time'));
        $params['billno'] = CommonClass::filter_input_init(INPUT_POST, 'billno', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['user_addtime'] = CommonClass::filter_input_init(INPUT_POST, 'useraddtime', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['user_addtime'] = $params['user_addtime'] ? $params['user_addtime'] : date('Y-m-d H:i:s', time() + 43200);
        if (
            empty($params['user_addtime']) ||
            !strtotime($params['user_addtime'])
        ) {
            $re = array('code' => $objCode->fail_save_onbank->code, 'errMsg' => '存入时间不对');
            break;
        }
        if (
        empty($params['billno'])
        ) {
            $re = array('code' => $objCode->fail_save_onbank->code, 'errMsg' => '订单号不能为空');
            break;
        }
        if (
        empty($params['card_user'])
        ) {
            $re = array('code' => $objCode->fail_save_onbank->code, 'errMsg' => '存款人真实姓名不能为空');
            break;
        }
        if (
        empty($params['money'])
        ) {
            $re = array('code' => $objCode->fail_save_onbank->code, 'errMsg' => '充值金额不能为空');
            break;
        }
        if (
        empty($params['pay_type'])
        ) {
            $re = array('code' => $objCode->fail_save_onbank->code, 'errMsg' => '支付类型不能为空');
            break;
        }
        if (
        empty($params['bank_id'])
        ) {
            $re = array('code' => $objCode->fail_save_onbank->code, 'errMsg' => '存款人银行id不能为空');
            break;
        }
        if (
        empty($params['bank_set_id'])
        ) {
            $re = array('code' => $objCode->fail_save_onbank->code, 'errMsg' => '收款银行不能为空');
            break;
        }
        if (
            $params['money'] < 0 ||
            !CommonClass::check_money($params['money'])
        ) {
            $re = array('code' => $objCode->fail_save_onbank->code, 'errMsg' => '请输入正确的金额数目');
            break;
        }
        $limit = $clientA->getSaveLimit(array('site_id' => SITE_ID, 'username' => $params['username']));
        if (!empty($limit) && $params['money'] < $limit['bank']['limit_min']) {
            $re = array('code' => $objCode->fail_save_less_than_limit->code, 'data' => array('bank' => $limit['bank']), 'errMsg' => '金额不能小于银行卡最低限额');
            break;
        }
        if (!empty($limit) && $params['money'] > $limit['bank']['limit_max']) {
            $re = array('code' => $objCode->fail_save_more_than_limit->code, 'data' => array('bank' => $limit['bank']), 'errMsg' => '金额不能大于银行卡最高限额');
            break;
        }
        $re = $clientA->saveBankOrder(json_encode($params));
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getprize",
     *   tags={"用户信息"},
     *   summary="查询奖金",
     *   description="返回最近10条结果<br>
    status：1:未激活 2：已激活 3：已提取 4：已失效",
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
     *     response="103011",
     *     description="'获取奖金列表数据！"
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="未登录！"
     *   )
     * )
     */
    case 'getprize'://获取奖金信息
        $params['site_id'] = SITE_ID;
        $params['username'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $re = $clientA->getBonusList($params);
        if ($re['code'] == $objCode->get_prizes->code) {
            $re['data']['total'] = 0;
            $datap = json_decode($re['info'], TRUE);
            //$re['data'] = array('prizes' => json_decode($re['info']));
            if ($datap) {
                $etime = time();
                $config = CommonClass::getSiteConfig($clientA);
                foreach ($datap as $i => $v) {
                    $datap[$i]['add_time'] = date("Y-m-d H:i:s", $v['add_time']);
                    $datap[$i]['end_time'] = date("Y-m-d H:i:s", $v['end_time']);
                    if ($v['status'] < 3) {
                        if ($v['end_time'] < time()) {
                            $datap[$i]['status'] = 4;
                            $clientA->changeBonusStatus(array('id' => $v['id'], 'status' => 4));
                            continue;
                        }
                        $clientB = new HproseSwooleClient(MY_TCP_CENTER_HOST); //小温
                        $str = SITE_ID . $v['username'] . date("Y-m-d", $v['add_time']) . date("Y-m-d", $etime) . date("H:i:s", $v['add_time']) . date("H:i:s", $etime);
                        $data = array(
                            'siteId' => SITE_ID,
                            'username' => $v['username'],
                            'betTimeBegin' => date("Y-m-d", $v['add_time']),
                            'betTimeEnd' => date("Y-m-d", $etime),
                            'startTime' => date("H:i:s", $v['add_time']),
                            'endTime' => date("H:i:s", $etime),
                            'key' => CommonClass::get_key_param($str, 5, 6)
                        );
                        //$result1 = $clientB->auditTotal(json_encode($data));
                        $result1 = $clientB->auditTotalTemp(json_encode($data));
                        $result2 = str_replace(array('"[', ']"', '\"', '\\\\', '"{', '}"'), array('[', ']', '"', '\\', '{', '}'), $result1);
                        $result = json_decode($result2, TRUE);
                        if ($result['returnCode'] == '900000') {
                            $usecodes = $clientA->getBonusUsecodes(array('site_id' => SITE_ID, 'username' => $v['username'], 'add_time' => $v['add_time'])); //统计已使用的打码量
                            $sum_money = isset($result['dataList']['totalValidamount']) ? $result['dataList']['totalValidamount'] : 0; //实际投注，总投注
                            if ($v['bonus_money_code'] <= ($sum_money - $usecodes)) {//达到打码量
                                if ($v['status'] == 1) {//从冻结（未激活）转态转为解冻状态（激活）
                                    $clientA->changeBonusStatus(array('id' => $v['id'], 'status' => 2));
                                    $datap[$i]['status'] = 2;
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
                $re['data']['prizes'] = $datap;
                $re['data']['total'] = $unget;
            }
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getprizesout",
     *   tags={"用户信息"},
     *   summary="提取奖金",
     *   description="返回结果<br>data:为失败原因",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="formData",
     *     description="奖金id",
     *     required=true,
     *     type="string",
     *   ),
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
     *     response="113040",
     *     description="提取奖金失败！"
     *   ),
     *   @SWG\Response(
     *     response="103039",
     *     description="提取奖金成功！"
     *   ),
     *   @SWG\Response(
     *     response="5",
     *     description="未达到打码量！"
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="未登录！"
     *   )
     * )
     */
    case 'getprizesout':
        $params['site_id'] = SITE_ID;
        $params['username'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $res = $clientA->isLoginUpload($params);
        if ($res['code'] != $objCode->is_login_status->code) {//用户已经登出，或异常
            unset($res['info']);
            echo CommonClass::ajax_return($res, $jsonp, $jsonpcallback);
            return false;
        }
        $session = json_decode($res['info'], TRUE);
        $id = CommonClass::filter_input_init(INPUT_POST, 'id', FILTER_SANITIZE_MAGIC_QUOTES);
        $one = $clientA->getBonusOne($id);
        if ($one['code'] == $objCode->get_prizes->code) {
            $par = json_decode($one['info'], TRUE);
            if ($par['end_time'] < time()) {
                $re = array("code" => $objCode->fail_take_bonus->code, 'data' => '奖金过时');
                break;
            }
            $etime = time();
            $config = CommonClass::getSiteConfig($clientA);
            $clientB = new HproseSwooleClient(MY_TCP_CENTER_HOST); //小温
            $str = SITE_ID . $par['username'] . date("Y-m-d", $par['add_time']) . date("Y-m-d", $etime) . date("H:i:s", $par['add_time']) . date("H:i:s", $etime);
            $data = array(
                'siteId' => SITE_ID,
                'username' => $par['username'],
                'betTimeBegin' => date("Y-m-d", $par['add_time']),
                'betTimeEnd' => date("Y-m-d", $etime),
                'startTime' => date("H:i:s", $par['add_time']),
                'endTime' => date("H:i:s", $etime),
                'key' => CommonClass::get_key_param($str, 5, 6)
            );
            $result1 = $clientB->auditTotal(json_encode($data));
            $result2 = str_replace(array('"[', ']"', '\"', '\\\\', '"{', '}"'), array('[', ']', '"', '\\', '{', '}'), $result1);
            $result = json_decode($result2, TRUE);
            if ($result['returnCode'] == '900000') {
                $usecodes = $clientA->getBonusUsecodes(array('site_id' => SITE_ID, 'username' => $par['username'], 'add_time' => $par['add_time'])); //统计已使用的打码量
                $sum_money = is_null($result['dataList']['totalValidamount']) ? 0 : $result['dataList']['totalValidamount']; //实际投注，总投注
                $par['can_use_codes'] = $sum_money - $usecodes;
                $par['sum_codes'] = $sum_money;
                if ($par['bonus_money_code'] <= ($sum_money - $usecodes)) {//达到打码量
                    if (substr($session['user_type'], -1) == 1) {//主副站修改
                        $clientC = new HproseSwooleClient(MY_TCP_MONEY_HOST_TEST); //光光
                    } else {
                        $clientC = new HproseSwooleClient(MY_TCP_MONEY_HOST); //光光
                    }
                    $key = CommonClass::get_key_param(SITE_WEB . $par['username'] . 'prize' . $par['id'], 5, 6);
                    $paramsb = array(
                        'fromKey' => SITE_WEB,
                        'siteId' => SITE_ID,
                        'username' => $par['username'],
                        'remitno' => 'prize' . $par['id'],
                        'remit' => $par['bonus_money'],
                        'transType' => 'in',
                        'fromKeyType' => '10047',
                        'key' => $key,
                        'memo' => '会员提出奖金'
                    );
                    $res = $clientC->transMoney(json_encode($paramsb));
                    $rkresult = json_decode($res, TRUE);
                    if ($rkresult['code'] != '100000') {//存款添加失败
                        $re = array("code" => $objCode->fail_take_bonus->code, 'data' => '存款失败', $rkresult);//失败
                    } else {
                        $re = $clientA->takeBonusOut($par['id']);
                    }
                } else {
                    $re = array("code" => 5, 'data' => '未达到打码量', 'sum_codes' => $sum_money, 'can_use_codes' => $par['can_use_codes'], 'result' => $result, 'params' => $data);
                }
            } else {//失败
                $re = array("code" => $objCode->fail_take_bonus->code, 'data' => '打码量查询失败', $result);
            }
        } else {//失败
            $re = array("code" => $objCode->fail_take_bonus->code, 'data' => 'ID错误');
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=saveuserdetial",
     *   tags={"用户信息"},
     *   summary="绑定银行卡",
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
     *     name="mobile",
     *     in="formData",
     *     description="手机号",
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
     *     name="bank_id",
     *     in="formData",
     *     description="银行id",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="bank_address",
     *     in="formData",
     *     description="银行归属地",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="bank_account",
     *     in="formData",
     *     description="银行卡号",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="realname",
     *     in="formData",
     *     description="真实姓名",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="bank_password",
     *     in="formData",
     *     description="银行取款密码",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="201027",
     *     description="绑定出款银行信息成功"
     *   ),
     *   @SWG\Response(
     *     response="211028",
     *     description="绑定出款银行信息失败"
     *   )
     *  )
     */
    case 'saveuserdetial'://绑定银行卡
        $params['site_id'] = SITE_ID;
        $params['username'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $params['mobile'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'mobile', FILTER_SANITIZE_MAGIC_QUOTES));
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['bank_id'] = CommonClass::filter_input_init(INPUT_POST, 'bank_id', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['bank_address'] = CommonClass::filter_input_init(INPUT_POST, 'bank_address', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['bank_account'] = CommonClass::filter_input_init(INPUT_POST, 'bank_account', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['bank_password'] = CommonClass::filter_input_init(INPUT_POST, 'bank_password', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['realname'] = CommonClass::filter_input_init(INPUT_POST, 'realname', FILTER_SANITIZE_MAGIC_QUOTES);
        if (
        empty($params['bank_id'])
        ) {
            $re = array('code' => $objCode->fail_connect_bank->code, 'data' => 'params error', 'errMsg' => '银行id必须填写');
            break;
        }
        if (
        empty($params['bank_address'])
        ) {
            $re = array('code' => $objCode->fail_connect_bank->code, 'data' => 'params error', 'errMsg' => '银行归属地必须填写');
            break;
        }
        if (
        empty($params['bank_account'])
        ) {
            $re = array('code' => $objCode->fail_connect_bank->code, 'data' => 'params error', 'errMsg' => '银行账号必须填写');
            break;
        }
        if (
        empty($params['bank_password'])
        ) {
            $re = array('code' => $objCode->fail_connect_bank->code, 'data' => 'params error', 'errMsg' => '银行取款密码必须填写');
            break;
        }
        if (
        empty($params['realname'])
        ) {
            $re = array('code' => $objCode->fail_connect_bank->code, 'data' => 'params error', 'errMsg' => '银行卡姓名必须填写');
            break;
        }
        if (
            !CommonClass::check_password_str($params['bank_password'], 4, 12) && $params['bank_password'] != '******'
        ) {
            $re = array('code' => $objCode->fail_connect_bank->code, 'data' => 'params error', 'errMsg' => '取款密码必须是4-12位之间的数字');
            break;
        }
        if (
        !CommonClass::check_mobile_str($params['mobile'])
        ) {
            $re = array('code' => $objCode->fail_connect_bank->code, 'data' => 'params error', 'errMsg' => '请输入正确的手机号码');
            break;
        }
        if ($params['bank_password'] == '******') {
            unset($params['bank_password']);
        } else {
            $params['bank_password'] = CommonClass::get_md5_pwd($params['bank_password']);
        }
        $re = $clientA->connectBankAccount($params);
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=saveuserphone",
     *   tags={"用户信息"},
     *   summary="绑定手机号",
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
     *     name="mobile",
     *     in="formData",
     *     description="手机号",
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
     *     response="201027",
     *     description="绑定出款银行信息成功"
     *   ),
     *   @SWG\Response(
     *     response="211028",
     *     description="绑定出款银行信息失败"
     *   )
     *  )
     */
    case 'saveuserphone'://绑定手机号
        $params['site_id'] = SITE_ID;
        $params['username'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $params['mobile'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'mobile', FILTER_SANITIZE_MAGIC_QUOTES));
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        if (empty($params['mobile'])) {
            $re = array('code' => $objCode->fail_connect_bank->code, 'data' => 'not enigth2', 'errMsg' => '手机号码必须填写');
            break;
        }
        if (!CommonClass::check_mobile_str($params['mobile'])) {
            $re = array('code' => $objCode->fail_connect_bank->code, 'data' => 'not enigth2', 'errMsg' => '请输入正确的手机号码');
            break;
        }
        $re = $clientA->userphone($params);
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=getsaveonlinebanks",
     *   tags={"存提款"},
     *   summary="获取在线存款银行列表",
     *   description="获取可供选择的银行，用于判断当前是否有可用的支付方式<br>is_weixin 是否是微信 is_zhifubao 是否是支付宝 都不是那就是在线网银",
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
     *     name="bank_code",
     *     in="formData",
     *     description="bank_code(微信 weixin 支付宝 zhifubao 网银 wangyin 默认网银)",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="103029",
     *     description="获取在线存款支持银行成功"
     *   ),
     *   @SWG\Response(
     *     response="113030",
     *     description="获取在线存款支持银行失败"
     *   )
     *  )
     */
    case 'getsaveonlinebanks'://获取线上存款供选择银行列表
        $params['site_id'] = SITE_ID;
        $params['username'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        //$params['ulevel'] = CommonClass::filter_input_init(INPUT_POST, 'ulevel',FILTER_SANITIZE_MAGIC_QUOTES);
        $params['bank_code'] = CommonClass::filter_input_init(INPUT_POST, 'bank_code', FILTER_SANITIZE_MAGIC_QUOTES);
        $re = $clientA->getSaveOnlineConfig($params);
        if ($re['code'] == $objCode->success_get_saveonline_banks->code) {
            $banks = json_decode($re['info']);
            $re['data'] = array('banks' => $banks);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=submitsaveonline",
     *   tags={"用户信息"},
     *   summary="线上存款提交订单",
     *   description="提交成功后会返回gurl ,前端需要跳转到改页面进行支付",
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
     *   @SWG\Parameter(
     *     name="vcode",
     *     in="formData",
     *     description="验证码",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="312011",
     *     description="验证码错误"
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
            $re = array('code' => $objCode->fail_save_online->code);
            break;
        }
        $limit = $clientA->getSaveLimit(array('site_id' => SITE_ID, 'ulevel' => $params['ulevel'], 'username' => $params['username']));
        if (!empty($limit) && $params['money'] < $limit['online']['limit_min']) {
            $re = array('code' => $objCode->fail_save_less_than_limit->code, 'data' => array('online' => $limit['online']));
            break;
        }
        if (!empty($limit) && $params['money'] > $limit['online']['limit_max']) {
            $re = array('code' => $objCode->fail_save_more_than_limit->code, 'data' => array('online' => $limit['online']));
            break;
        }
        //print_r($params);
        $re = $clientA->saveOnLineOrder($params);
        if ($re['code'] == $objCode->success_save_online->code) {//在线存款底单提交成功
            $re['data'] = array('gurl' => $re['info']);
        }
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=dsgame",
     *   tags={"公共信息"},
     *   summary="获取ds小游戏列表",
     *   description="",
     *   consumes={"application/x-www-form-urlencoded; charset=UTF-8"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="gt",
     *     in="formData",
     *     description="游戏类型(只返回该游戏类型下面的游戏列表,传0所有)",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="pt",
     *     in="formData",
     *     description="平台（1 DS，2 AG,3 BBIN,4 MG）",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="3",
     *     description="获取失败"
     *   ),
     *   @SWG\Response(
     *     response="1",
     *     description="获取成功"
     *   )
     *  )
     */
    case 'dsgame':
        $gt = CommonClass::filter_input_init(INPUT_POST, 'gt', FILTER_SANITIZE_MAGIC_QUOTES);
        $pt = CommonClass::filter_input_init(INPUT_POST, 'pt', FILTER_SANITIZE_MAGIC_QUOTES);
        $pt = empty($pt) ? 1 : $pt;
        $re = $clientA->getDSGame($gt, $pt);
        if ($re) {
            $re = json_decode($re, TRUE);
            if (isset($re['total'])) {
                $re['code'] = 1;
            } else {
                $re['code'] = 2;
            }
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
        $re = $clientA->imgSearch(IMG_SITE_ID, 'logo');
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
        $re = $clientA->imgSearch(IMG_SITE_ID, 'rotate');
        if ($re['code'] == $objCode->success_get_img_con->code) {//获取图片站信息成功
            $re['data'] = json_decode($re['info']);
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
        $re = $clientA->imgSearch(IMG_SITE_ID, 'promotion');
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
        $re = $clientA->imgSearch(IMG_SITE_ID, 'newstag');
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
        $re = $clientA->imgSearch(IMG_SITE_ID, 'news', $tag);
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
        $re = $clientA->imgSearch(IMG_SITE_ID, 'notice');
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
        $re = $clientA->getLimitTypeOne();
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
        $re = $clientA->getLimitTypeTwo($type_one_id);
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
        $re = $clientA->getLimitData($type_two_id, SITE_ID);
        if ($re['code'] == $objCode->success_get_limit->code) {
            $re['data'] = json_decode($re['info']);
        }
        break;
    /***完善资料部分*********************************************/
    /**
     * @SWG\Post(
     *   path="/action.php?action=getuserdetail",
     *   tags={"用户信息"},
     *   summary="获取用户信息",
     *   description="",
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
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="201031",
     *     description="获取用户详细资料成功"
     *   ),
     *   @SWG\Response(
     *     response="211032",
     *     description="获取用户详细资料失败"
     *   )
     *  )
     */
    case 'getuserdetail':
        $params['username'] = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['site_id'] = SITE_ID;
        $re = $clientA->getUserDetails($params);
        $re['data'] = json_decode($re['info'], TRUE);
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=updateuserdetail",
     *   tags={"用户信息"},
     *   summary="更新用户资料",
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
     *   @SWG\Parameter(
     *     name="zip_code",
     *     in="formData",
     *     description="邮编",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="register_address",
     *     in="formData",
     *     description="注册地址",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     description="email",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="qq",
     *     in="formData",
     *     description="qq",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="date_of_birth",
     *     in="formData",
     *     description="生日（2017-1-14 16:05:38）",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="bank_account",
     *     in="formData",
     *     description="银行卡号",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="get_password",
     *     in="formData",
     *     description="出款密码",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="realname",
     *     in="formData",
     *     description="真实姓名",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="nickname",
     *     in="formData",
     *     description="昵称",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="211014",
     *     description="用户已经登出"
     *   ),
     *   @SWG\Response(
     *     response="201033",
     *     description="修改用户详细资料成功"
     *   ),
     *   @SWG\Response(
     *     response="211034",
     *     description="修改用户详细资料失败"
     *   )
     *  )
     */
    case 'updateuserdetail':
        $params['username'] = CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES);
        if (!CommonClass::check_username_str($params['username'], 4, 12)) {
            $re = array('code' => $objCode->fail_to_reg->code, 'errMsg' => '用户名必须为4-12位之间的字母和数字');
            break;
        }
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['site_id'] = SITE_ID;
        $params['zip_code'] = CommonClass::filter_input_init(INPUT_POST, 'zip_code', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['register_address'] = CommonClass::filter_input_init(INPUT_POST, 'register_address', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['email'] = CommonClass::filter_input_init(INPUT_POST, 'email', FILTER_SANITIZE_MAGIC_QUOTES);
        if ($params['email']) {
            if (!preg_match("/^([a-z0-9A-Z]+[-|\\.]?)+[a-z0-9A-Z]@([a-z0-9A-Z]+(-[a-z0-9A-Z]+)?\\.)+[a-zA-Z]{2,}$/", $params['email'])) {
                $re = array('code' => $objCode->errMsgApp->code, 'errMsg' => '请输入正确的电子邮箱');
                break;
            }
            $re = array('email' => $params['email'], 'company' => SITE_ID, 'ip' => $ip);
            $re = $clientA->checkEmail($re);
            if ($re['code'] == $objCode->is_have_email->code) {
                $re = array('code' => $objCode->fail_to_reg->code, 'errMsg' => '该电子邮箱已经存在');
                break;
            }
        }
        $params['qq'] = CommonClass::filter_input_init(INPUT_POST, 'qq', FILTER_SANITIZE_MAGIC_QUOTES);
        if ($params['qq']) {
            if (!preg_match("/^[1-9][0-9]{4,11}$/", $params['qq'])) {
                $re = array('code' => $objCode->fail_to_agentreg->code, 'errMsg' => '请输入正确的qq号');
                break;
            }
            $re = array('qq' => $params['qq'], 'company' => SITE_ID, 'ip' => $ip);
            $re = $clientA->checkQq($re);
            if ($re['code'] == $objCode->is_have_qq->code) {
                $re = array('code' => $objCode->fail_to_reg->code, 'errMsg' => '该qq已经存在');
                break;
            }
        }
        if (empty($params['email']) && empty($params['qq'])) {
            $re = array('code' => $objCode->fail_to_reg->code, 'errMsg' => '电子邮箱和QQ号至少填写一个');
            break;
        }
        $params['date_of_birth'] = strtotime(CommonClass::filter_input_init(INPUT_POST, 'date_of_birth', FILTER_SANITIZE_MAGIC_QUOTES));
        $params['bank_account'] = CommonClass::filter_input_init(INPUT_POST, 'bank_account', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['get_password'] = CommonClass::filter_input_init(INPUT_POST, 'get_password', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['realname'] = CommonClass::filter_input_init(INPUT_POST, 'realname', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['nickname'] = CommonClass::filter_input_init(INPUT_POST, 'nickname', FILTER_SANITIZE_MAGIC_QUOTES);
        if (!empty($params['nickname']) && !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]{2,8}$/u", $params['nickname'])) {
            $re = array('code' => $objCode->fail_to_reg->code, 'errMsg' => '昵称必须是2-8位的中文、字母和数字');
            break;
        }
        $re = $clientA->updateUserDetails($params);
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
        $re = $clientA->getSpreadPromos($params);
        if ($re['code'] == $objCode->success_get_spread_promos->code) {
            $re['data'] = json_decode($re['info'], TRUE);
        }
        $config = CommonClass::getSiteConfig($clientA);
        $re['appurl'] = APPURL;
        break;
    /**
     * @SWG\Post(
     *   path="/action.php?action=checksign",
     *   tags={"用户信息"},
     *   summary="签到",
     *   description="返回结果
    <br>获取签到记录：{log(签到历史),num(当月签到总数)}
    <br>执行签到：num: 当月签到总数, points:本次奖励 , s_points:本次累计奖励, n_num: 下次获得累计积分需要签到次数, n_s_points:下次累计可获得积分",
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
     *     name="check",
     *     in="formData",
     *     description="0：获取签记录  1：执行签到",
     *     required=false,
     *     type="string"
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
    case 'checksign'://签到
        $params['site_id'] = SITE_ID;
        $params['username'] = strtolower(CommonClass::filter_input_init(INPUT_POST, 'username', FILTER_SANITIZE_MAGIC_QUOTES));
        $params['oid'] = CommonClass::filter_input_init(INPUT_POST, 'oid', FILTER_SANITIZE_MAGIC_QUOTES);
        $params['check'] = CommonClass::filter_input_init(INPUT_POST, 'check', FILTER_SANITIZE_MAGIC_QUOTES);//是否是签到
        $re = $clientA->ernieSignlogin($params);
        if (isset($re['info'])) {
            $re['data'] = json_decode($re['info'], true);
        }
        $re['code'] = $re['error'] ? $re['error'] : '1001';
        if ($re['code'] != 1000) {
            CommonClass::myLog($params['username'] . '签到失败:' . json_encode($_REQUEST) . '结果：' . json_encode($re), 'DEBUG');
        } else {
            CommonClass::myLog($params['username'] . '签到成功:' . json_encode($_REQUEST) . '结果：' . json_encode($re), 'DEBUG');
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
        $re = array(
            'code' => '1000',
            'data' => array(
                'time' => time()
            )
        );
        break;
    default:
        session_start();
        //$_SESSION['test'] = 1111111111111;
//        echo $_SESSION['test'];
        //phpinfo();
        exit();
        try {
            //$re = $clientA->hello(array('ip'=>'127.0.0.1','site_id'=>1));
            $params = array('site_id' => 1, 'ulevel' => 1, 'ip' => $ip, 'money' => 1000, 'username' => 'ceshi997', 'pay_type' => 'online');
            $re = $clientA->addUserCheck($params);
            echo "<pre>";
            print_r($re);
            echo "</pre>";
        } catch (Exception $e) {
            echo "<pre>";
            print_r($e);
            echo "</pre>";
        }
        break;
}
unset($re['info']);
echo CommonClass::ajax_return($re, $jsonp, $jsonpcallback);
?>
