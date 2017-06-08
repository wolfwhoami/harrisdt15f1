<?php

class Short_hand
{
    public $match_status =
        [
            'ypassword'=>'原密码',
            'password'=>'现密码',
        ];

    /**
     * 检测传入值
     * @param $param
     * @param array $neglect (neglegible)
     * @return array|bool
     */
    public function Trace_and_return($param, $neglect = [])
    {
        global $objCode;
        foreach ($param as $key => $value) {
            if (!empty($neglect)) {
                if (in_array($key, $neglect)) continue;
            }
            if (empty($value)) {
                $key = isset($this->match_status[$key])?$this->match_status[$key]: $key;
                return $mess = [
                    'code'=> $objCode->paramerror1->code,
                    'errMsg' => $key . " 为空值"
                ];
            }
        }
        return true;
    }
    /**
     * 检测传入值
     * @param string $param
     * @return mixed
     */
    public function create_param($param = '')
    {
        foreach ($param as $key => $value) {
            $data[$value] = strtolower(CommonClass::filter_input_init(INPUT_POST, $value, FILTER_SANITIZE_MAGIC_QUOTES));
        }
        return $data;
    }

    /**
     * get array from the log_args_write() and write to the log dynamically
     * @param array $arr
     * @param $level
     * @return bool|string
     */
    public function han_log($arr = [], $level)
    {
        if (empty($arr)) {
            return "拼接日志数组为空";
        } else {
            $log = '';
            foreach ($arr as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $key => $value) {
                        $log = empty($log) ? $key . " = " . $value . "\n" : $log . $key . " = " . $value . "\n";
                    }
                    CommonClass::myLog($log, $level);
                } else {
                    CommonClass::myLog('拼接日志输入string 值请检查参数', $level);
                }
            }
            return true;
        }
    }
}

?>
