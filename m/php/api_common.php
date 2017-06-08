<?php
/* Common Functions Collection */

/**
 * 一次性调用 hprose 通常第一次的 时候调用
 * @param $get_input (input parameters)
 * @param $f (function name)
 * @param array $neglect (neglegible)
 * @return mixed
 * author : harris
 */
function prepare_goto_hprose($get_input, $f, &$params, $neglect = [])
{
    $check_empty = check_param($get_input, $params, $neglect);
    if ($check_empty !== true) {
        return $check_empty;
    }
    return goto_center_api($f, $params);
}

/**
 * 检查input是否正确
 * @param $get_input
 * @param $params
 * @param array $neglect
 * @return array|bool
 * author : Harris
 */
function check_param($get_input, &$params, $neglect = [])
{
    $check_return = new Short_hand();
    if (!((is_bool($get_input) && ($get_input !== false)))) {
        $params = empty($params) ? [] : array_filter($params);
        $create_result = $check_return->create_param($get_input);
        $params = empty($params) ? $create_result : array_merge($params, $create_result);
        $params['site_id'] = SITE_ID;
        array_push($neglect, 'site_id');
        $check_empty = $check_return->Trace_and_return($params, $neglect);
        return $check_empty;
    }
    return true;

}

/**
 * 调用hprose函数通常次数调用时用
 * @param $f
 * @param string $params
 * @return array
 * author : Harris
 */
function goto_center_api($f, $params = '')
{
    global $clientA;
    try {
        $re = $clientA->$f($params);
        if (!($re)) {
            $re = [
                'code' => 10001,
                'errMsg' => '返回空值'
            ];
        }
    } catch (Exception $e) {
        $re = [
            'code' => 10001,
            'errMsg' => '函数调用失败请联系客服'
        ];
        $log_to_write = [
            'hprose请求数据' => null,
            'goto' => $f,
            '原因' => $e->getMessage() . "\n" . $e->getTraceAsString(),
            'level' => 'Hprose_error'
        ];
        if (is_array($params) || is_object($params)) {
            $log_to_write['hprose请求数据'] = json_encode($params, JSON_UNESCAPED_UNICODE);
        } else {
            $log_to_write['hprose请求数据'] = $params;
        }
        log_args_write($log_to_write);
    }
    return $re;
}

/**
 * 调用中心api时用
 * @return array|mixed
 * author : Harris
 */
function goto_center_with_paras()
{
    global $clientA;
    try {
        $args = func_get_args();
        $f = $args[0];
        array_shift($args);
        $re =call_user_func_array([$clientA, $f], $args);
        if (!($re)) {
            $re = [
                'code' => 10001,
                'errMsg' => '返回空值'
            ];
        }
    } catch (Exception $e) {
        $args = func_get_args();
        $re = [
            'code' => 10001,
            'errMsg' => '函数调用失败请联系客服'
        ];
        $log_to_write = [
            'hprose请求数据' => json_encode($args, JSON_UNESCAPED_UNICODE),
            'goto' => $args[0],
            '原因' => $e->getMessage() . "\n" . $e->getTraceAsString(),
            'level' => 'Hprose_error'
        ];
        log_args_write($log_to_write);
    }
    return $re;
}

/**
 * @param $f
 * @param $constant
 * @param string $params
 * @return array
 * author : Harris
 */
function goto_coffee_mix($f, $constant, $params = '')
{
    try {
        $clientD = new HproseSwooleClient($constant);
        $re = $clientD->$f($params);
        if (!($re)) {
            $re = [
                'code' => 10001,
                'errMsg' => '返回空值'
            ];
        }
    } catch (Exception $e) {
        $re = [
            'code' => 10001,
            'errMsg' => '函数调用失败请联系客服'
        ];
        $log_to_write = [
            'swoole_hprose请求数据' => json_encode($params, JSON_UNESCAPED_UNICODE),
            '地址' => $constant,
            'goto' => $f,
            '原因' => $e->getMessage() . "\n" . $e->getTraceAsString(),
            'level' => 'Hprose_error'
        ];
        log_args_write($log_to_write);
    }
    return $re;
}

/**
 * Create the formula for probability with min max of possible outcomes
 * @param $play
 * @param $possible_out_comes
 * @return mixed
 */
function prob_formula_create($play, &$possible_out_comes)
{
    $min = 0;
    $max = 0;
    $i = 0;
    $possible_out_comes = 0;
    foreach ($play as $key => $value) {
        $max = empty($max) ? $value : $min + $value;//first min + first max;
        $possible_out_comes += $value;
        $arr[$key] = [
            'min' => $min,
            'max' => $max
        ];
        $min = $max;
        $i++;
    }
    return $arr;
}

/**
 * @param $arr
 * @param int $possible_out_comes
 * @param int $loop
 * @return int|string
 */
function drawing($arr, $possible_out_comes = 100, $loop = 1)
{
    for ($i = 0; $i < $loop; $i++) {
        $rnd = rand(1, $possible_out_comes);
        foreach ($arr as $k => $v) {
            if ($rnd > $v['min'] && $rnd <= $v['max']) {
                return $k;
            }
        }
    }
}

/**
 * array type should be
 * $a1 = ['label'=>'description'];
 * usage log_args_write(array1,array2,....);
 * Create array to write log and get the arguments dynamically and send them to han_log
 * @return array|bool|string
 */
function log_args_write()
{
    $log = [];
    $marker = 0;
    $k = '';
    $level = '';
    $numArgs = func_num_args(); //number of args
    $args = func_get_args(); //get args
    foreach ($args as $index => $arg) {
        if (is_array($arg)) {
            if (array_key_exists("level", $arg)) {
                $level = $arg['level'];
                unset($arg['level']);
            }
            array_push($log, $arg);
            $marker++;
        } else {
            $k = empty($k) ? $index . 'args =' . $arg : $k . "<br>" . $index . 'args =' . $arg;
        }
    }
    if ($marker < $numArgs) {
        return $mess = [
            'code' => 10001,
            'errMsg' => $k . ' 不是arrray值'
        ];
    }
    $sh = new Short_hand();
    $status = $sh->han_log($log, $level);//writ log
    return $status;
}

function thirdparty_online_pay($payment_name = 'weixin')
{
    global $ip, $objCode, $clientA;
    if (isset($_SESSION['vcode_session']))
    {
        unset($_SESSION['vcode_session']);
        session_destroy();
    }
    $re['code'] = '1001';
    $re['status'] = 'fail';
    //信息不全，提交订单失败
    if (isset($_REQUEST['money'])) {
        if (!CommonClass::check_money($_REQUEST['money']) || $_REQUEST['money'] <= 0) {
            $re['errMsg'] = "输入金额错误，请稍后再试！";
            return $re;
        }
    }
    $params['add_time'] = time();
    $params['user_ip'] = $ip;
    if ($payment_name == 'third_banks') {
        $get_input = ['username', 'oid', 'money', 'bank_code'];

    } else {
        $get_input = ['username', 'oid', 'money'];
        $params['bank_code'] = $payment_name;
    }
    $re = prepare_goto_hprose($get_input, 'isLoginUpload', $params);
    if (is_array($re) && array_key_exists('errMsg', $re)) {
        return $re;
    } else if ($re['code'] != $objCode->is_login_status->code) {//用户已经登出，或异常
        $re['errMsg'] = "你已登出！";
        return $re;
    }
    $re['data'] = json_decode($re['info'], TRUE);
    unset($re['info']);
    if ($re['data']['money_status'] != 1 || $re['data']['deposit'] != 1) {
        $re = [
            'code' => $objCode->is_not_allow_save->code,
            'errMsg' => "您的存/取款操作已被停用，请联系客服",
        ];
        return $re;
    }
    $params['ulevel'] = $re['data']['ulevel'];
    $limit = goto_center_api('getSaveLimit', $params);
    if (!empty($limit) && $params['money'] < $limit['online']['limit_min']) {
        $re['errMsg'] = "存款金额低于最低存款限制(¥{$limit['online']['limit_min']})！";
        return $re;
    }
    if (!empty($limit) && $params['money'] > $limit['online']['limit_max']) {
        $re['errMsg'] = "存款金额高于最高存款限制(¥{$limit['online']['limit_max']})！";
        return $re;
    }
    $re = goto_center_api('saveOnLineOrder', $params);
    if ($re['code'] == $objCode->success_save_online->code) {//在线存款底单提交成功
        $config = CommonClass::getSiteConfig($clientA);
        if (SAVE_TEST) {
            $re['info'] = $re['info'] . "&debug=true";
        }
        $re = [
            'status' => 'success',
            'code' => '10000',
            'data' => [
                'url' => $re['info'],
                'payname' => $re['payname'],
                'billno' => $re['billno']
            ]
        ];
    } else if ($re['code'] == $objCode->is_not_allow_save->code) {
        $re['errMsg'] = "{$objCode->is_not_allow_save->message}";
    }
//            else{
//
//            }
    return $re;
}

/**
 * anytime to china time
 * @param $time must be Y-m-d H:i:s format
 * @return string
 * author : Harris
 */
function china_time($time, $format = 'Y-m-d H:i:s')
{
    $datetime = new DateTime($time);
    $cn_time = new DateTimeZone('Asia/Shanghai');
    $datetime->setTimezone($cn_time);
    return $datetime->format($format);
}

/**
 * anytime to east america time
 * @param $time must be Y-m-d H:i:s format
 * @return string
 * author : Harris
 */
function EUSA_time($time, $format = 'Y-m-d H:i:s')
{
    $datetime = new DateTime($time);
    $cn_time = new DateTimeZone('Etc/GMT+4');
    $datetime->setTimezone($cn_time);
    return $datetime->format($format);
}

/**
 * 删除过期日志
 * author: harris
 * @return int
 */
function del_expire_log($day = null)
{
    $day = is_null($day) ? 60 * 60 * 24 * 2 : $day;
    $log_num = 0;//图片数量
    $log_path = '/../../Logs/';
    $p_path = __DIR__ . $log_path;
    $dh = @opendir($p_path);
    while ($file = @readdir($dh)) {
        if ($file != '.' && $file != '..') {
            $file = realpath($p_path . '/' . $file);
            if (is_file($file)) {
                $log_num++;
                $m_time = filemtime($file);//图片最新修改时间
                $m_time_debug = date('Y-m-d H:i:s', $m_time);//图片最新修改时间
                if (time() - $m_time >= $day) {
                    $log_to_write = [
                        '日志文件' => $file,
                        '状态' => '删除',
                        'level' => 'DEL_LOG'
                    ];
                    log_args_write($log_to_write);
                    @unlink($file);
                }
            }
        }
    }
    closedir($dh);
    return $log_num;
}
del_expire_log(60 * 60 * 24 * 2);

?>