<?php
class webchat {
    /**
     * 判断是否登录
     * @param $params['company'] 公司
     * @param $params['username'] 用户名
     * @param  $params['oid'] oid
     * 
     */
    public function isLoginP($params) {
        $sk='dssession_' . $params['company'] . '_' . $params['username'];
        $this->redis = $this->redis_connect(0);

        $session = $this->redis->get($sk);
        if ($session) {
            $data = json_decode($session, TRUE);
            return $data['oid'] == $params['oid'] ? [
                'code' => $this->objCode['is_login_status']['code']
            ] : [
                'code' => $this->objCode['is_not_login_status']['code']
            ];
        } else {
            return [
                'code' => $this->objCode['is_not_login_status']['code']
            ];
        }
    }

}


