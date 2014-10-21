<?php

namespace Jason;

/**
 * Jason Framework主类
 * @package Jason
 * @author Jason Wang <jasonwx@163.com>
 * @since 1.0.0
 *
 * @property \Jason\Http\Headers $header
 * @property \Jason\Http\Request $request
 */
class Jason
{

    const VERSION = '1.0.0';

    public $env;
    public static $config;

    /**
     * @var \Jason\Http\Headers
     */
    public $header;

    /**
     * @var \Jason\Http\Request
     */
    public $request;

    /**
     * @var \Jason\Log
     */
    public $log;

    /**
     * Jason PSR-0 autoloader
     */
    private static function autoload($className)
    {
        $thisClass = str_replace(__NAMESPACE__ . '\\', '', __CLASS__);

        $baseDir = __DIR__;

        if (substr($baseDir, -strlen($thisClass)) === $thisClass) {
            $baseDir = substr($baseDir, 0, -strlen($thisClass));
        }

        $className = ltrim($className, '\\');
        $fileName = $baseDir;
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        if (file_exists($fileName)) {
            require $fileName;
        }
    }

    /**
     * Register Jason's PSR-0 autoloader
     */
    private static function registerAutoloader()
    {
        spl_autoload_register(__NAMESPACE__ . "\\Jason::autoload");
    }

    public function __construct($config = null)
    {
        self::registerAutoloader();
        self::$config = $config;
        if ($this->isCli()) {

        } else {
            $this->env = \Jason\Environment::getInstance()->properties;
            $this->header = new \Jason\Http\Headers($this);
            $this->request = new \Jason\Http\Request($this);
        }
        $this->log = new \Jason\Log($this, $this->isCli());
        $this->log->setRequestTime(microtime(true) * 10000);
    }

    public function run()
    {
        set_error_handler(array('\Jason\Jason', 'handleErrors'));
        \Jason\Route::router($this, $this->isCli());
    }

    /**
     * 在这里执行Controller
     * @param Object $targetObject
     * @param Array $route
     */
    public function call($targetObject, $route)
    {
        $response = $targetObject->$route['jason.route.methodname']();
//        \Jason\Middleware\Model::commit();
        $this->response($response, $route['jason.route.header']);
    }

    /**
     * Convert errors into ErrorException objects
     *
     * This method catches PHP errors and converts them into \ErrorException objects;
     * these \ErrorException objects are then thrown and caught by Slim's
     * built-in or custom error handlers.
     *
     * @param  int $errno The numeric type of the Error
     * @param  string $errstr The error message
     * @param  string $errfile The absolute path to the affected file
     * @param  int $errline The line number of the error in the affected file
     * @return bool
     * @throws \ErrorException
     */
    public static function handleErrors($errno, $errstr = '', $errfile = '', $errline = '')
    {
        if (!($errno & error_reporting())) {
            return;
        }

        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    public function defaultNotFound()
    {
        $text = array(
            "error_no" => 80000,
            "exception" => "javax.ws.rs.WebApplicationException"
        );
        $this->response($text, Route::$returnType);
    }

    public function response($text = null, $header = null)
    {
        $this->log->httpLog($text);
        if (!$this->isCli()) {
            header($header);
            header("X-Powered-By:Jason Framework");
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Headers: rep, token, sign");
            if ($header == \Jason\Http\Response::HEADER_JSON) {
                echo $text ? json_encode($text) : '';
            } else {
                echo is_array($text) ? json_encode($text) : $text;
            }
//            flush();
        }
    }

    public static function getConfig($key = null)
    {
        if ($key) {
            if (isset(self::$config[$key])) {
                return self::$config[$key];
            } else {
                return null;
            }
        } else {
            return self::$config;
        }
    }

    private function isCli()
    {
        return (\PHP_SAPI === 'cli' || empty($_SERVER['REMOTE_ADDR']));
    }

}
