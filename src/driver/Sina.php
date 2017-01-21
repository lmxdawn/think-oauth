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

namespace lmxdawn\oauth\driver;

use lmxdawn\oauth\Driver;

/**
 * 新浪 第三方登录操作类
 */
class Sina extends Driver
{

    const VERSION = "2.0";
    const BASE_URL = "https://api.weibo.com";//基础url地址
    const GET_AUTH_CODE_URL = self::BASE_URL ."/oauth2/authorize";//获取Authorization Code 地址
    const GET_ACCESS_TOKEN_URL = self::BASE_URL ."/oauth2/access_token";//获取Authorization Code获取Access Token
    const GET_OPENID_URL = self::BASE_URL ."/oauth2/get_token_info";//获取用户的openID

    protected $options = [
        'prefix'    => 'qq_',//前缀
        'appkey' =>  '564358851',//分配给应用的appid。
        'appsecret'  =>  '98b0adaf40ceb5965049c17d42758081',//分配给网站的appkey。
        'callback' =>  'http://localhost/tp5/public/index.php/index/index/callback',//回调地址
        'scope' =>  'get_user_info',//请求用户授权时向用户显示的可进行授权的列表。可填写的值是API文档中列出的接口，以及一些动作型的授权（目前仅有：do_like），如果要填写多个接口名称，请用逗号隔开。例如：scope=get_user_info,list_album,upload_pic,do_like不传则默认请求对接口get_user_info进行授权。建议控制授权项的数量，只传入必要的接口名称，因为授权项越多，用户越可能拒绝进行任何授权。
    ];

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options = []) {

        if (!empty($options) && is_array($options)){
            $this->options = array_merge($this->options,$options);
        }
    }



    /**
     * 登录
     */
    public function login(){

        //应用 appid
        $appkey = $this->options['appkey'];
        //回调地址
        $callback = $this->options['callback'];
        //可授权列表
        $scope = $this->options['scope'];

        //唯一随机串防CSRF攻击
        $state = $this->setState($this->options['prefix']);

        //构造请求参数列表
        $keysArr = array(
            "client_id" => $appkey,
            "redirect_uri" => urlencode($callback),
            "state" => $state,
            "scope" => $scope
        );

        //登录地址
        $login_url =  $this->combineURL(self::GET_AUTH_CODE_URL, $keysArr);

        header("Location:$login_url"); // 跳转

    }

    /**
     * 获取accesstoken
     * @return $this
     */
    public function accessToken(){

        $state = $this->getState($this->options['prefix']);
        //验证state防止CSRF攻击
        if($_GET['state'] != $state){
            $this->showError("30001",'The state does not match. You may be a victim of CSRF.');
        }

        //应用 appkey
        $appkey = $this->options['appkey'];
        //回调地址
        $callback = $this->options['callback'];
        // appsecret
        $appsecret = $this->options['appsecret'];

        //请求参数列表
        $keysArr = array(
            "grant_type" => "authorization_code",
            "client_id" => $appkey,
            "redirect_uri" => urlencode($callback),
            "client_secret" => $appsecret,
            "code" => $_GET['code']
        );

        //构造请求access_token的url
        $token_url = self::GET_ACCESS_TOKEN_URL;
        $response = $this->_request($token_url,'post',$keysArr);

        $res = json_decode($response,true);
        if(isset($res['error_code'])){
            $this->showError($res['error_code'], $res['error']);
        }


        $access_token = isset($res['access_token']) ? $res : [];

        $this->access_token = $access_token;

        return $this;
    }


    /**
     * 获取openid
     * @return $this
     */
    public function openid(){

        $access_token = $this->access_token;

        if (empty($access_token)){
            $this->showError('40001', 'access_token not null');
        }

        if (empty($access_token['uid'])){
            $this->showError('40002', 'uid not null');
        }

        $this->openid = $access_token['uid'];

        return $this;

    }

    public function userInfo(){

    }



}