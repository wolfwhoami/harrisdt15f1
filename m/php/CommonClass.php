<?php

require_once("Log.class.php");

set_error_handler(array('CommonClass', '_error_handler'), error_reporting());//php 报错
set_exception_handler(array('CommonClass', '_exception_handler'));// 异常

class CommonClass
{
    public static $_SiteConfigStatus = FALSE;
    public static $config = '';
    public static $_clientA;

    private static $_redishost = REDIS_HOST;
    private static $_redisport = REDIS_PORT;
    private static $_redis_pass = REDIS_PASS;
    public static $_redis;

    static protected $storage = null;

    public function __construct()
    {

    }

    public function get_user_ip()
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ips = explode(',', $_SERVER["HTTP_X_FORWARDED_FOR"]);
            $ip = $ips[0];
        } else if (isset($_SERVER["HTTP_X_REAL_IP"]))
            $ip = $_SERVER["HTTP_X_REAL_IP"];
        else if (isset($_SERVER["HTTP_CLIENT_IP"]))
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        else if (isset($_SERVER["REMOTE_ADDR"]))
            $ip = $_SERVER["REMOTE_ADDR"];
        else if (getenv("HTTP_X_REAL_IP"))
            $ip = getenv("HTTP_X_REAL_IP");
        else if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else
            $ip = "Unknown";
        return $ip;
    }

    public function get_md5_pwd($pwd)
    {
        return trim($pwd);
    }

    public function jsonRemoveUnicodeSequences($struct)
    {
        return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($struct));
    }

    /**
     * ajax返回包装
     * @param type $params 返回的参数
     * @param type $jsonpcallback 跨域回调函数名
     * @param type $jsonp 类型json，jsonp,
     */
    public function ajax_return($params, $jsonp = 'json', $jsonpcallback = '')
    {
        if (isset($params['re'])) {
            unset($params['re']);
        }
        if (isset($params['pra'])) {
            unset($params['pra']);
        }
        $re = '';
        if ($jsonp == 'json') {
            /*$jj = new self();
            $re = $jj->jsonRemoveUnicodeSequences($params);*/
            $re = json_encode($params);
        } else if ($jsonp == 'jsonp') {
            $re = $jsonpcallback . "(" . json_encode($params) . ")";
        }

        //日志记录
        //报错日志
        self::clear_echo('报错：');
        return $re;
    }

    public function check_username_str($str, $minlength, $maxlength)
    {
        $reg = "/^[a-z0-9]+$/";
        if (preg_match($reg, $str)) {
            $len = strlen($str);
            if ($len < $minlength || $len > $maxlength) {
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            return FALSE;
        }
    }

    public function check_truename_str($str)
    {
        $Ch = "/^[\x{4e00}-\x{9fa5}]+$/u";
        $En = "/^([a-zA-Z]+)$/";
        if (preg_match($Ch, $str) || preg_match($En, $str)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function check_email_str($str)
    {
        if (filter_var($str, FILTER_VALIDATE_EMAIL) === FALSE) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function check_agentname_str($str, $minlength, $maxlength)
    {
        $reg = "/^d[a-z0-9]+$/";
        if (preg_match($reg, $str)) {
            $len = strlen($str);
            if ($len < $minlength || $len > $maxlength) {
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            return FALSE;
        }
    }

    public function check_password_str($str, $minlength, $maxlength)
    {
        $reg = "/^[A-Za-z0-9]+$/";
        if (preg_match($reg, $str)) {
            $len = strlen($str);
            if ($len < $minlength || $len > $maxlength) {
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            return FALSE;
        }
    }

    public function check_mobile_str($str)
    {
        $reg = "/^1[3|4|5|7|8|][0-9]{9}/";
        if (preg_match($reg, $str) || empty($str)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function check_qq_str($str)
    {
        $reg = "/^[0-9]+$/";
        if (preg_match($reg, $str)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function check_money($money)
    {
        $reg = "/^\d+(\.\d+)?$/";
        if (preg_match($reg, $money)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 生成base64加密的sessionkey，方便电子游戏调用
     * 随机7位+去掉两位等号(base64_encode(site_id + _ + 用户名))+随机两位
     * @param type $username
     * @return type
     */
    public function get_session_key_forgane($username)
    {
        $data = strtoupper(md5(rand(11111, 99999) . time() . 'aadsadsdsafdsafdsafdafhjhjklhabbcc'));
        $str1 = substr($data, 2, 7);
        $str2 = substr($data, 12, 2);
        $key = SITE_ID . '_' . $username;
        return $str1 . str_replace('=', '', base64_encode($key)) . $str2;
    }
    /**
     * 生成个人信息缓存哈希键值
     * @param type $company
     * 公司id
     * @param type $username
     * 用户名
     * @return type
     */
    public function get_session_key($company, $username)
    {
        return 'dssession_' . $company . '_' . $username;
    }

    /**
     * 生成key作为调用各平台的api参数
     * @param type $str
     * @param type $pre
     * @param type $next
     * @return type
     */
    public function get_key_param($str, $pre, $next)
    {
        $data = strtolower(md5(rand(11111, 99999) . time() . 'aadsadsdsafdsafdsafdafhjhjklhabbcc'));
        $str1 = substr($data, 2, $pre);
        $str2 = substr($data, 12, $next);
        return $str1 . md5($str) . $str2;
    }

    /**
     * 生成订单编号
     * @param type $company
     * @param type $username
     * @param type $money
     * @return type
     */
    public function get_billno($company, $username, $money = '')
    {
        return 'no_' . substr(md5($money . $username . $company . time() . 'aadsadsdsafdsafdsafdafhjhjklhabbcc' . rand(11111, 99999)), 8, -8);
    }

    /**
     * 需要配置环境支持iconv，否则中文参数不能正常处理
     * @param type $data
     * @param type $key
     * @return type
     */
    static function dcEncrypt($data, $key)
    {
        $key = iconv("GB2312", "UTF-8", $key);
        $data = iconv("GB2312", "UTF-8", $data);

        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
            $key = pack("H*", md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;
        return md5($k_opad . pack("H*", md5($k_ipad . $data)));
    }

    static function getLoadingPage($logo, $color = '#3d3d3d')
    {
        if ($color == '#ffffff') {
            $fcolor = '#3d3d3d';
        } else {
            $fcolor = 'white';
        }
        $str = '<html><head><style>
            body{background-color:' . $color . '; text-align:center}
                </style>

        </head><body>
        <div style="width: 1000px; height: 197px; position: absolute; top: 50%; left: 50%; margin-left: -500px; margin-top: -100px;">
            <div style="background:url(http://static.ds88online.com/public/images/Loading/' . $logo . '.png?i=3.104) no-repeat;height: 61px;width: 134px;margin: 0 auto;"></div>
         <br>
         <div style="background:url(http://static.ds88online.com/public/images/Loading/loading1.gif) no-repeat;height: 23px;width: 220px;margin: 0 auto;"></div>
         <br>
         <span><a style="font: small-caption; color:' . $fcolor . ';">加载中，请稍候...</a></span>
         </div>
        </body></html>';
        return $str;
    }

    static function is_wap()
    {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        $uachar = "/(nokia|sony|ericsson|mot|samsung|sgh|lg|sie|philips|panasonic|alcatel|lenovo|cldc|midp|wap|mobile)/i";
        if (($ua == '' || preg_match($uachar, $ua)) && !strpos(strtolower($_SERVER['REQUEST_URI']), 'wap')) {
            return true;
        } else {
            return false;
        }
    }

    //DS开牌结果
    static function disassemble_type($bankerResult)
    {
        $poker_number = [
            [
                '1' => '庄',
                '2' => '闲',
                '3' => '和'
            ],
            [
                '',
                '庄对',
                '闲对',
                '庄对闲对'
            ],
            [
                '1' => '小',
                '2' => '大'
            ]
        ];
        if (!empty($bankerResult)) {
            $poker_result = [];
            foreach ($bankerResult as $pk_child_key => $pk_child_val) {
                if ($pk_child_key == 3) {
                    break;
                }
                $poker_result[] = $poker_number[$pk_child_key][$pk_child_val];
            }
            $poker_result[3] = $bankerResult[3];
            return $poker_result;
        }
    }

    //DS牌型分解
    static function disassemble_poker($poker_array)
    {
        $poker_point = [];
        foreach ($poker_array as $point_key => $point_val) {
            foreach ($point_val as $point_child_key => $point_child_val) {
                $poker_point[$point_key][$point_child_key] = $point_child_val;
            }
        }
        return $poker_point;
    }

    //DS会员下注详情
    static function amount_bet($details)
    {
        $xztype = [
            'BC_BANKER' => '庄',
            'BC_PLAYER' => '闲',
            'BC_TIE' => '和',
            'BC_BANKER_PAIR' => '庄对',
            'BC_PLAYER_PAIR' => '闲对',
            'BC_BIG' => '大',
            'BC_SMALL' => '小',
            'BC_BANKER_INSURANCE' => '庄保险',
            'BC_PLAYER_INSURANCE' => '闲保险'
        ];
        if (isset($details[0])) {
            $player_details = [];
            foreach ($details as $rd_child_key => $rd_child_val) {
                foreach ($rd_child_val as $xz_key => $xz_value) {
                    if ($xz_key == 'betType') {
                        $player_details[$rd_child_key][$xz_key] = $xztype[$xz_value];
                    } elseif ($xz_key == 'betTime') {
                        $player_details[$rd_child_key][$xz_key] = date('Y-m-d H:i:s', $xz_value / 1000);
                    } else {
                        $player_details[$rd_child_key][$xz_key] = $xz_value;
                    }
                }
            }
            return $player_details;
        }
    }

    //BBIN牌型分解
    static function disassemble_poker_bbin($poker_str)
    {
        $poker_bbin = explode('*', $poker_str);
        $poker_array = [];
        foreach ($poker_bbin as $pk_key => $pk_val) {
            $result[$pk_key] = explode(',', $pk_val);
        }
        foreach ($result as $re_key => $re_val) {
            foreach ($re_val as $child_key => $child_val) {
                $rechild = explode('.', $child_val);
                switch ($rechild[0]) {
                    case 'S':
                        $poker_array[$re_key][$child_key] = $rechild[1];
                        break;
                    case 'H':
                        $result_h = $rechild[1] + 13;
                        $poker_array[$re_key][$child_key] = "$result_h";
                        break;
                    case 'C':
                        $result_c = $rechild[1] + 26;
                        $poker_array[$re_key][$child_key] = "$result_c";
                        break;
                    case 'D':
                        $result_d = $rechild[1] + 39;
                        $poker_array[$re_key][$child_key] = "$result_d";
                        break;
                }
            }
        }
        return $poker_array;
    }

    static function pxceshi()
    {
        $ceshi_r = [
            "liveMemberReportDetails" => [
                [
                    "betType" => "BC_BANKER",
                    "betAmount" => 10,
                    "winLossAmount" => 19.5,
                    "betTime" => 1432970871000
                ],
                [
                "betType" => "BC_SMALL",
                "betAmount" => 10,
                "winLossAmount" => 0.0,
                "betTime" => 1432970770000
                ]
            ]
        ];
        $ceshi = '{"bankerResult":[2,2,2,8]}';

        $ceshi_d = '{"pokerList":[[25,51,8],[49,15,28]]}';

        $ceshi_c = 'H.6,C.7,D.13*C.12,S.13,C.13';

        $ceshi_r = CommonClass::amount_bet($ceshi_r); //DS会员下注详情
        $ceshi = CommonClass::disassemble_type($ceshi);//DS开牌结果
        $ceshi_d = CommonClass::disassemble_poker($ceshi_d);//DS牌型分解
        //$ceshi_c = CommonClass::disassemble_poker_bbin($ceshi_c);//BBIN牌型分解

        echo "<pre>";
        echo "<br />DS会员下注详情:<br />";
        print_r($ceshi_r);
        echo "<br /><br />DS开牌结果:<br />";
        print_r($ceshi);
        echo "<br /><br />DS牌型分解:<br />";
        print_r($ceshi_d);
        //echo "<br /><br />BBIN牌型分解:<br />";
        //print_r($ceshi_c);
        echo "</pre>";
    }

    static function get_notice_type($site_type, $type)
    {
        switch ($type) {
            case 1://滚动公告
                if ($site_type == 1) {//主站
                    $re = 1;
                } else if ($site_type == 2) {
                    $re = 11;
                }
                break;
            case 2://弹出公告
                if ($site_type == 1) {//主站
                    $re = 2;
                } else if ($site_type == 2) {
                    $re = 21;
                }
                break;
            case 3://会员中心表格显示
                if ($site_type == 1) {//主站
                    $re = 99;
                } else if ($site_type == 2) {
                    $re = 98;
                }
                break;
        }
    }

    static function bbwait()
    {
        $str = '        <html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<style>
body{
	background: #1e1d26 url(http://static.ds88online.com/public/images/Loading/bbwait.png) no-repeat center;
	}
</style>
</head>
<body>
</body>
</html>';
        return $str;
    }

    static function Vcode($msg)
    {
        $str = ' <html>
<head>
<title>自动关闭窗口</title>
<meta http-equiv="content-Type" content="text/html;charset=UTF-8">
</head>
<body>
<script>
Alert("' . $msg . '");
//自动关闭提示框
function Alert(str) {
    var msgw,msgh,bordercolor;
    msgw=334;//提示窗口的宽度
    msgh=145;//提示窗口的高度
    titleheight=25 //提示窗口标题高度
    bordercolor="#a8a3a3";//提示窗口的边框颜色
    titlecolor="#BCD";//提示窗口的标题颜色
    var sWidth,sHeight;
    //获取当前窗口尺寸
    sWidth = document.body.offsetWidth;
    sHeight = document.body.offsetHeight;
//    //背景div
    var bgObj=document.createElement("div");
    bgObj.setAttribute("id","alertbgDiv");
    bgObj.style.position="absolute";
    bgObj.style.top="0";
    bgObj.style.background="#FFF";
    bgObj.style.filter="progid:DXImageTransform.Microsoft.Alpha(style=3,opacity=25,finishOpacity=75";
    bgObj.style.opacity="0.6";
    bgObj.style.left="0";
    bgObj.style.width = sWidth + "px";
    bgObj.style.height = sHeight + "px";
    bgObj.style.zIndex = "10000";
    document.body.appendChild(bgObj);
    //创建提示窗口的div
    var msgObj = document.createElement("div")
    msgObj.setAttribute("id","alertmsgDiv");
    msgObj.setAttribute("align","center");
    msgObj.style.background="white";
    msgObj.style.border="5px solid " + bordercolor;
    msgObj.style.position = "absolute";
    msgObj.style.left = "50%";
    msgObj.style.font="17px/1.6em Verdana, Geneva, Microsoft YaHei, Helvetica, sans-serif";
    //窗口距离左侧和顶端的距离
    msgObj.style.marginLeft = "-225px";
    //窗口被卷去的高+（屏幕可用工作区高/2）-150
    msgObj.style.top = document.body.scrollTop+(window.screen.availHeight/2)-150 +"px";
    msgObj.style.width = msgw + "px";
    msgObj.style.height = msgh + "px";
    msgObj.style.textAlign = "center";
    msgObj.style.lineHeight ="25px";
    msgObj.style.zIndex = "10001";
    document.body.appendChild(msgObj);
    //提示信息标题
    var title=document.createElement("h4");
    title.setAttribute("id","alertmsgTitle");
    title.setAttribute("align","left");
    title.style.margin="0";
    title.style.padding="8px";
    title.style.background = "#900";
    title.style.filter="progid:DXImageTransform.Microsoft.Alpha(startX=20, startY=20, finishX=100, finishY=100,style=1,opacity=75,finishOpacity=100);";
    title.style.opacity="0.75";
    title.style.height="23px";
    title.style.font="15px Verdana, Geneva, Microsoft YaHei, Helvetica, sans-serif";
    title.style.color="#fff";
    title.innerHTML="提示信息";
    document.getElementById("alertmsgDiv").appendChild(title);
    //提示信息
    var txt = document.createElement("p");
    txt.setAttribute("id","msgTxt");
    txt.style.margin="16px 0";
    txt.style.font="17px Verdana, Geneva, Microsoft YaHei, Helvetica, sans-serif";
    txt.style.lineHeight ="60px";
    txt.innerHTML = str;
    document.getElementById("alertmsgDiv").appendChild(txt);
    //设置关闭时间
    window.setTimeout("closewin()",2000);
}
function closewin() {
    document.body.removeChild(document.getElementById("alertbgDiv"));
    document.getElementById("alertmsgDiv").removeChild(document.getElementById("alertmsgTitle"));
    document.body.removeChild(document.getElementById("alertmsgDiv"));
    self.close();
}
</script>
</body>
</html>';
        return $str;
    }

    static function getSiteConfig($clientA)
    {
        if (!self::$_SiteConfigStatus) {
            self::$config = $clientA->getSiteSet(SITE_ID);
            if (isset(self::$config['api_config'])) {
                self::$_SiteConfigStatus = TRUE;
                foreach (self::$config['api_config'] as $k => $v) {
                    if ($k != 'SITE_ID') {
                        define($k, $v);
                    }
                }
                return self::$config;
            } else {
                return 0;
//                echo "网络错误";
//                exit();
            }
        } else {
            return self::$config;
        }
    }

    static function gethttpA()
    {
        if (empty(self::$_clientA)) {
            self::$_clientA = new HproseHttpClient(MY_HTTP_HOST);
            return self::$_clientA;

        } else {
            return self::$_clientA;
        }
    }

    /**
     * redis连接
     */
    public function redisconnect($num)
    {
        if (empty(self::$_redis)) {
            self::$_redis = new Redis();
            self::$_redis->connect(self::$_redishost, self::$_redisport);
            if (self::$_redis_pass != '') {
                self::$_redis->auth(self::$_redis_pass);
            }
        }
        self::$_redis->select($num);
        return self::$_redis;
    }

    /**
     * 生成稽核key
     * @param type $company
     * @param type $username
     * @return type
     */
    static function get_rule_key($company, $username)
    {
        return 'dsrule_' . $company . '_' . $username;
    }

    static function filter_input_init($input_type, $name, $id = FILTER_DEFAULT, $special = true)
    {
        $value = trim(filter_input($input_type, $name, $id));
        if ($special) {
            $value = htmlspecialchars($value);
        }
        return $value;
    }

    //日志记录
    static function myLog($msg, $level = 'INFO')
    {
        if (!self::$storage) {
            self::$storage = new \Log\File();
        }
        $today = date('Y-m-d H:i:s', time());
        $cn_today_date=china_time($today,'y_m_d');
        $log_file = '';
        if ($level != 'INFO') {
            $log_file = $level . '_' . $cn_today_date . '.log';
        }
        self::$storage->write("{$level}: {$msg}", $log_file);
    }

    //记录报错信息
    static function clear_echo($messege)
    {
        $error = ob_get_contents();
        if (!empty($error)) {
            self::myLog($messege . $error, 'ERR');
        }
        ob_clean();
        return $error;
    }

    static function _error_handler($errno, $errstr, $errfile, $errline)
    {
        self::myLog('文件：' . $errfile . '出错,错误号：' . $errno . '，第' . $errline . '行，错误内容是：' . $errstr, 'error_log');
    }

    static function _exception_handler($exception)
    {
        self::myLog('抛出异常：' . $exception->getMessage(), 'exception');
    }

    /**
     * 创建表单
     * @data  表单内容
     * @gateway 支付网关地址
     */
    static function buildForm($data, $gateway) {
        $sHtml = "<form id='fromsubmit' name='fromsubmit' action='" . $gateway . "' method='post'>";
        while (list ($key, $val) = each($data)) {
            $sHtml.= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        $sHtml.= "</form>";
        $sHtml.= "<script>document.forms['fromsubmit'].submit();</script>";

        return $sHtml;
    }
}

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

