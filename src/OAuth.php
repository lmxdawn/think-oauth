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



/**
 * 登录类
 */
class OAuth
{

    // 实例
    protected static $instance = [];



    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    private function __construct($options = []) {


    }

    // 私有化克隆方法
    private function __clone() {}


    /**
     * 实例化 OAuth
     * @param null $name OAuth 类名 (如:Qq)
     * @return mixed 该类的实例
     * @throws \Exception 找不到该类
     */
    public static function oauth($name = null) {

        if (isset(self::$instance[$name])){
            return self::$instance[$name];
        }

        $class = "lmxdawn\\oauth\\driver\\" . $name;

        if (class_exists($class)){
            $oauth = new $class();
        }else{
            throw new \Exception('class not exists:' . $class);
        }

        self::$instance[$name] = $oauth;

        return $oauth;
    }
}