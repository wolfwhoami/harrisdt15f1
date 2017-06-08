<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Log;

class File {

    protected $config  =   [
        'log_time_format'   =>  ' c ',
        'log_file_size'     =>  2097152,
        'log_path'          =>  '',
    ];

    // 实例化并传入参数
    public function __construct($config= []){
        $this->config['log_path'] = $_SERVER['DOCUMENT_ROOT'].'/Logs/';
        $this->config   =   array_merge($this->config,$config);
    }

    /**
     * 日志写入接口
     * @access public
     * @param string $log 日志信息
     * @param string $destination  写入目标
     * @return void
     */
    public function write($log,$log_file='') {
        $hostname = gethostname();
        $today = date('Y-m-d H:i:s', time());
        $now = ' 北京时间：' . $this->china_time($today);
        $now = $now.' 美东时间：' . $this->EUSA_time($today);
        $cn_today_date=$this->china_time($today,'y_m_d');
        if(empty($log_file)){
            $destination = $this->config['log_path'].$cn_today_date.'.log';
        }else{
            $destination = $this->config['log_path'].$log_file;
        }
        // 自动创建日志目录
        $log_dir = dirname($destination);
        if (!is_dir($log_dir)) {
//            mkdir($log_dir, 0755, true);
            mkdir($log_dir, 0777, true);
        }        
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        $time='';
        if(is_file($destination) && floor($this->config['log_file_size']) <= filesize($destination) ){
            $time = time();
            rename($destination,dirname($destination).'/'.$time.'-'.basename($destination));
        }
        error_log("[{$now}] ".$time.'_'.$hostname.'_'.$_SERVER['REMOTE_ADDR']."\r\n ".$_SERVER['HTTP_USER_AGENT']."\r\n ".$_SERVER['REQUEST_URI']."\r\n{$log}\r\n", 3,$destination);
    }

    /**
     * anytime to china time
     * @param $time must be Y-m-d H:i:s format
     * @return string
     * author : Harris
     */
    private function china_time($time,$format='Y-m-d H:i:s')
    {
        $datetime = new \DateTime($time);
        $cn_time = new \DateTimeZone('Asia/Shanghai');
        $datetime->setTimezone($cn_time);
        return $datetime->format($format);
    }
    /**
     * anytime to east america time
     * @param $time must be Y-m-d H:i:s format
     * @return string
     * author : Harris
     */
    private function EUSA_time($time,$format='Y-m-d H:i:s')
    {
        $datetime = new \DateTime($time);
        $cn_time = new \DateTimeZone('Etc/GMT+4');
        $datetime->setTimezone($cn_time);
        return $datetime->format($format);
    }
}
