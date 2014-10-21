<?php

namespace Jason;

use Jason\Http\Headers;
use Jason\Http\Response;

/**
 * 路由处理器
 *
 * @package Jason
 * @author  Jason Wang <jasonwx@163.com>
 */
class Route
{

    /**
     * 路由表缓存容器
     * @var Array
     */
    private static $routingTable;

    public static $returnType = Response::HEADER_JSON;

    /**
     * 制定get请求的路由规则
     * @param string $uri 请求地址
     * @param array $target 目标controller
     * @param type $responseType 响应头 通过\Jason\Http\Response获取
     */
    public static function get($uri, $target, $responseType = \Jason\Http\Response::HEADER_JSON)
    {
        self::set(\Jason\Http\Request::METHOD_GET, $responseType, $uri, $target);
    }

    /**
     * 制定post请求的路由规则
     * @param string $uri 请求地址
     * @param array $target 目标controller
     * @param type $responseType 响应头 通过\Jason\Http\Response获取
     */
    public static function post($uri, $target, $responseType = \Jason\Http\Response::HEADER_JSON)
    {
        self::set(\Jason\Http\Request::METHOD_POST, $responseType, $uri, $target);
    }

    /**
     * 制定cli的路由规则
     * @param string $uri 请求地址
     * @param array $target 目标controller
     */
    public static function cli($uri, $target, $responseType = \Jason\Http\Response::HEADER_HTML)
    {
        self::set('CLI', $responseType, $uri, $target);
    }

    /**
     * 路由分析器
     * 根据当前pathinfo和设置的路由表分析出应执行哪个controller
     * @param \Jason\Jason $app
     */
    public static function router(\Jason\Jason $app, $cli = false)
    {
        if ($cli) {
            $arr = getopt("c:f:");
            if (isset($arr['c']) && isset($arr['f'])) {
                $method = 'CLI';
                $route = self::getRouteTable($method, $arr['c'] . '&' . $arr['f']);
            } else {
                $app->defaultNotFound();
                return;
            }
        } else {
            $method = $app->request->getMethod();
            $route = self::getRouteTable($method, $app->env['PATH_INFO']);
        }
        if (!is_null($route)) {
            if (class_exists($route['jason.route.namespace'])) {
                $class = new $route['jason.route.namespace']($app);
                if (method_exists($class, $route['jason.route.methodname'])) {
                    //将成功通过路由表的pathinfo对应的controller实例注入到Jason框架主类中
                    self::$returnType = $route['jason.route.header'];
                    return $app->call($class, $route);
                }
            }
        }
        $app->defaultNotFound();
    }

    /**
     * 创建一条路由数据
     *
     * @param String $method 请求类型
     * @param String $responseType 返回HTTP头类型
     * @param String $uri PATHINFO
     * @param String $target 目标Controller
     */
    private static function set($method, $responseType, $uri, $target)
    {
        self::$routingTable[$method][$uri] = array(
            "jason.route.namespace" => $target[0],
            "jason.route.methodname" => $target[1],
            "jason.route.header" => $responseType
        );
    }

    /**
     * 获取设置的路由数据
     * @param String $method 请求类型
     * @param String $path PATHINFO
     * @return null/Array
     */
    private static function getRouteTable($method = null, $path = null)
    {
        if ($method) {
            if (isset(self::$routingTable[$method])) {
                return isset(self::$routingTable[$method][$path]) ? self::$routingTable[$method][$path] : null;
            } else {
                return null;
            }
        } else {
            return self::$routingTable;
        }
    }

}
