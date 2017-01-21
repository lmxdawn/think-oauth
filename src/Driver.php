<?php
// +----------------------------------------------------------------------
// | ThinkPHP 5 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 .
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 黎明晓 <lmxdawn@gmail.com>
// +----------------------------------------------------------------------


namespace lmxdawn\oauth;

use think\Session;

/**
 * 第三方登录基础类
 */
abstract class Driver
{
    //参数配置
    protected $options = [];

    //状态值，用于第三方应用防止CSRF攻击
    protected $state;

    //access_token
    protected $access_token = [];

    // openid
    protected $openid;


    /**
     * 调用登录
     * @return mixed
     */
    abstract public function login();

    /**
     * 获取accesstoken
     * @return mixed
     */
    abstract public function accessToken();

    /**
     * 获取openid
     * @return $this
     */
    abstract public function openid();

    /**
     * 获取用户信息
     * @return mixed
     */
    abstract public function userInfo();


    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param  string $url    请求URL
     * @param  string $method 请求方法
     * @param  string  $data   POST的数据，GET请求时该参数无效
     * @param  array  $param  GET参数数组
     * @param  string $ship   #参数
     * @return array          响应数据
     */
    protected function _request($url, $method = 'get', $data = '', $param = [], $ship = ''){
        $opts = array(
            CURLOPT_TIMEOUT        => 30,//要求结果为字符串且输出到屏幕上
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        );
        /* 根据请求类型设置特定参数 */
        $opts[CURLOPT_URL] = $url . '?' . http_build_query($param) . $ship;
        if(strtoupper($method) != 'get'){
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $data;
            if(is_string($data)){ //发送JSON数据
                $opts[CURLOPT_HTTPHEADER] = array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($data),
                );
            }
        }
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        //发生错误，抛出异常
        if($error) exit('请求发生错误 : '.$error);
        return  $data;
    }

    /**
     * 拼接url
     * @param string $baseURL   基于的url
     * @param array  $keysArr   参数列表数组
     * @return string           返回拼接的url
     */
    public function combineURL($baseURL,$keysArr){
        $combined = $baseURL."?";
        $valueArr = array();

        foreach($keysArr as $key => $val){
            $valueArr[] = "$key=$val";
        }

        $keyStr = implode("&",$valueArr);
        $combined .= ($keyStr);

        return $combined;
    }

    /**
     * 获取 state 值
     * @param string $prefix
     * @return string
     */
    protected function getState($prefix = ''){
        if (empty($prefix)) return '';

        $state = '';
        if (!empty($this->state)){
            $state =  $this->state;
        }else{
            $state = Session::get($prefix . 'state');
            $this->state = $state;
        }
        return $state;

    }

    /**
     * 设置 state 值
     * 作用：用于第三方应用防止CSRF攻击
     * @param string $prefix
     * @param string $value
     * @return string
     */
    protected function setState($prefix = '',$value = ''){
        if (empty($prefix)) return '';

        $state = $value ? $value : md5(uniqid(rand(), TRUE));

        Session::set($prefix . 'state',$state);

        return $state;

    }

    /**
     * 打印错误信息
     * @param        $code 错误码
     * @param string $msg 错误信息
     */
    public function showError($code, $msg = ''){
        echo "<meta charset=\"UTF-8\">";

        echo "<h3>error:</h3>$code";
        echo "<h3>msg  :</h3>$msg";
        exit();

    }

}