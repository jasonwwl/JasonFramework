<?php

namespace Jason;

/**
 * Jason框架的所有运行时参数处理接收器
 * 
 * @package Jason
 * @author  Jason Wang <jasonwx@163.com>
 */
class Environment
{

    /**
     * 运行时参数的容器
     * @var Array
     */
    public $properties;

    /**
     * 本类的实例容器
     * @var \Jason\Environment
     */
    protected static $environment;

    /**
     * 获取本类实例
     * @param Boolean $refresh 是否需要刷新运行时参数
     * @return \Jason\Environment
     */
    public static function getInstance($refresh = false)
    {
        if (is_null(self::$environment) || $refresh) {
            self::$environment = new self();
        }

        return self::$environment;
    }

    private function __construct($settings = null)
    {
        if ($settings) {
            $this->properties = $settings;
        } else {
            $env = array();

            //The HTTP request method
            $env['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'];

            //The IP
            $env['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];

            // Server params
            $scriptName = $_SERVER['SCRIPT_NAME']; // <-- "/foo/index.php"
            $requestUri = $_SERVER['REQUEST_URI']; // <-- "/foo/bar?test=abc" or "/foo/index.php/bar?test=abc"
            $queryString = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : ''; // <-- "test=abc" or ""
            // Physical path
            if (strpos($requestUri, $scriptName) !== false) {
                $physicalPath = $scriptName; // <-- Without rewriting
            } else {
                $physicalPath = str_replace('\\', '', dirname($scriptName)); // <-- With rewriting
            }
            $env['SCRIPT_NAME'] = rtrim($physicalPath, '/'); // <-- Remove trailing slashes
            // Virtual path
            $env['PATH_INFO'] = substr_replace($requestUri, '', 0, strlen($physicalPath)); // <-- Remove physical path
            $env['PATH_INFO'] = str_replace('?' . $queryString, '', $env['PATH_INFO']); // <-- Remove query string
            $env['PATH_INFO'] = '/' . ltrim($env['PATH_INFO'], '/'); // <-- Ensure leading slash
            // Query string (without leading "?")
            $env['QUERY_STRING'] = $queryString;

            //Name of server host that is running the script
            $env['SERVER_NAME'] = $_SERVER['SERVER_NAME'];

            //Number of server port that is running the script
            $env['SERVER_PORT'] = $_SERVER['SERVER_PORT'];

            //HTTP request headers (retains HTTP_ prefix to match $_SERVER)
            $headers = \Jason\Http\Headers::extract($_SERVER);
            foreach ($headers as $key => $value) {
                $env[$key] = $value;
            }

            //Is the application running under HTTPS or HTTP protocol?
            $env['jason.url_scheme'] = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https';

            //Input stream (readable one time only; not available for multipart/form-data requests)
            $rawInput = @file_get_contents('php://input');
            if (!$rawInput) {
                $rawInput = '';
            }
            $env['jason.input'] = $rawInput;

            //Error stream
            $env['jason.errors'] = @fopen('php://stderr', 'w');

            $this->properties = $env;
        }
    }

}
