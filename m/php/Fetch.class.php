<?php

class Fetch {
    public $debug = false;
    public function __construct($url) {
        header("Content-Type: text/html; charset=utf-8");
        $this->api_url = $url;
    }
    protected function GetUrl($params, $str) {
        foreach ($params as $key => $value) {
            $str.="&$key=$value";
        }
        return $this->api_url . $str;
    }
    
    protected function PostUrl($params, $action) {
        $params['action'] = $action;
        return $params;
    }
    
    protected function PostData($param) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        //curl_setopt($ch, CURLOPT_TIMEOUT_MS,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        $html_data = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_status == '200') {
            if ($this->debug) {
                echo '<br>访问地址:', $this->api_url, '<br>返回内容:';
                echo '<pre>';
                print_r($html_data);
                echo '</pre>';
            }
            return $html_data;
        } else {
            if ($this->debug) {
                echo 'http_status:' . $http_status . '<br>';
                echo '<br>访问地址:', $this->api_url, '<br>';
                echo '连接超时或无法获取数据';
                 echo '<br>请求参数：<br>';
                echo '<pre>';
                print_r($param);
                echo '</pre>';
            }
            return $html_data;
        }
    }

    protected function GetData($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect: 100-continue" ));
        //curl_setopt($ch, CURLOPT_TIMEOUT_MS,1);
        $html_data = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
//        if($http_status == '100'){
//            sleep(1);
//            $ch = curl_init();
//            curl_setopt($ch, CURLOPT_URL, $url);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
//            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect: 100-continue" ));
//            $html_data = curl_exec($ch);
//            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//            curl_close($ch);
//            
//        }
        
        
        if ($http_status == '200') {
            if ($this->debug) {
                echo '<br>访问地址:', $url, '<br>返回内容:';
                echo '<pre>';
                print_r($html_data);
                echo '</pre>';
            }
            return $html_data;
        } else {
            if ($this->debug) {
                echo 'http_status:' . $http_status . '<br>';
                echo '<br>访问地址:', $url, '<br>';
                echo '连接超时或无法获取数据';
            }
            array('code'=>123,'rstatus'=>$http_status);
            return $html_data;
        }
    }

    public function Login($params,$post='post') {
        if($post == 'get'){
            $url = $this->GetUrl($params, '?action=login');
            return $this->GetData($url);
        }else{
            $url = $this->PostUrl($params, 'login');
            return $this->PostData($url);
        }
    }

    public function CheckUsrBalance($params,$post='post') {
        if($post == 'get'){
            $url = $this->GetUrl($params, '?action=balance');
            return $this->GetData($url);
        }else{
            $url = $this->PostUrl($params, 'balance');
            return $this->PostData($url);
        }
    }

    public function Transfer($params,$post='post') {
        if($post == 'get'){
            $url = $this->GetUrl($params, '?action=transfer');
            return $this->GetData($url);
        }else{
            $url = $this->PostUrl($params, 'transfer');
            return $this->PostData($url);
        }
    }
//---------------------------------waterloopwm 2015-12-03 start-------------------------------------

    /*
    * 新接口统一POST提交请求
    */
    protected function NewPostData($url,$param) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        //curl_setopt($ch, CURLOPT_TIMEOUT_MS,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        $html_data = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_status == '200') {
            if ($this->debug) {
                echo '<br>访问地址:', $url, '<br>返回内容:';
                echo '<pre>';
                print_r($html_data);
                echo '</pre>';
            }
            return $html_data;
        } else {
            $html_data['errMsg']='http_status:' . $http_status . '<br>访问地址:'.$url.'连接超时或无法获取数据_'.$http_status;
            return json_encode($html_data);
        }
    }

    /*
    * 新接口查询余额函数封装
    */
    public function NewCheckUsrBalance($params,$post='post') {
        $url=$this->api_url.'queryBalance/';
        return $this->NewPostData($url,$params);
    }

    /*
    * 新接口转账函数封装
    */
    public function NewTransfer($params,$post='post') {
        $url=$this->api_url.'transfer/';
        return $this->NewPostData($url,$params);
    }

    public function NewLogin($params,$post='post') {
        $url=$this->api_url.'login/';
        return $this->NewPostData($url,$params);
    }
    //-----------------------------------waterloopwm 2015-12-03 end-----------------------------------------

    //积分查询
    public function PointCheckUsrBalance($params,$post='post') {
        $url=$this->api_url.'getPoint/';
        return $this->NewPostData($url,$params);
    }
}
