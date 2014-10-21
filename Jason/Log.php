<?php

namespace Jason;

/**
 * 日志处理器
 * 
 * @package Jason
 * @author  Jason Wang <jasonwx@163.com>
 */
class Log
{

    /**
     * 环信日志
     */
    const EASEMOB = 'easemob';

    /**
     * 应用层通信日志
     */
    const HTTP = 'http';

    /**
     * 系统级错误日志
     */
    const ERROR = 'error';

    /**
     * 数据库驱动层错误日志
     */
    const DB_ERROR = 'errordb';

    /**
     * 应用层错误日志
     */
    const APP_ERROR = 'errorapp';

    /**
     * 极光推送日志
     */
    const JPUSH = 'jpush';

    /**
     * 实例化Jason的容器
     * @var \Jason\Jason 
     */
    private $jason;

    /**
     * 日志存放地址
     * @var String
     */
    private $dir;
    private $requestTime = null;
    private $cli = false;

    /**
     * 初始化
     * @param \Jason\Jason $app
     */
    public function __construct(\Jason\Jason $app, $isCli = false)
    {
        $this->jason = $app;
        $this->dir = $this->jason->getConfig('LOG');
        $this->cli = $isCli;
    }

    public function setRequestTime($time)
    {
        $this->requestTime = round($time / 10000);
    }

    /**
     * 写入日志
     * @param String $method 日志类型
     * @param String $log 日志内容
     */
    public function writer($method, $log)
    {
        $file = $this->dir . DIRECTORY_SEPARATOR . $method;
        if (!is_dir($file)) {
            mkdir($file);
        }
        $file .= DIRECTORY_SEPARATOR . date("Y-m-d");
        $header = $this->header();
        $request = $this->request();
        file_put_contents($file, $header . $request . $log . "-------------------------\n", FILE_APPEND);
    }

    /**
     * 应用层通信日志
     * @param String $response 应答内容
     */
    public function httpLog($response = null)
    {
        if (!$this->jason->getConfig('LOG_SAVE_REQUEST')) {
            return;
        }

    }

    /**
     * 系统级错误日志
     * @param String $truck truck信息
     */
    public function errorLog($truck)
    {
        $error = \Jason\Util\Json::array2JsonFormat($truck) . "\n";
        $this->writer(self::ERROR, $error);
    }

    /**
     * 系统数据库层错误日志
     * @param String $truck truck信息
     */
    public function dbErrorLog($truck)
    {
        $error = \Jason\Util\Json::array2JsonFormat($truck) . "\n";
        $this->writer(self::DB_ERROR, $error);
    }

    /**
     * 应用层错误日志
     * @param String $truck 
     */
    public function appErrorLog($truck)
    {
        $error = \Jason\Util\Json::array2JsonFormat($truck) . "\n";
        $this->writer(self::APP_ERROR, $error);
    }

    /**
     * 日志头部内容
     * @return String
     */
    private function header()
    {
        $log = "";
        $log.='[' . date("H:i:s", $this->requestTime) . '] ';
        if ($this->cli) {
            $log .= 'CLI';
        } else {
            $log .= $this->jason->request->getMethod() . ': ';
            $log .= $this->jason->env['PATH_INFO'];
            $log .= ' rep:' . $this->jason->header->getRep();
            $log .= ' (' . $this->jason->env['REMOTE_ADDR'] . ")";
        }
        return $log . "\n";
    }

    /**
     * 日志头部请求内容
     * @return String
     */
    private function request()
    {
        if ($this->cli) {
            return null;
        }
        $log = "Request:\n";
        $data = $this->jason->request->params();
        if ($data) {
            $data = array_merge(array(
                '(*)token' => $this->jason->header->getToken(),
                '(*)sign ' => $this->jason->header->getSign(),
                '(*)rep  ' => $this->jason->header->getRep()
                    ), $data);
            $_text = array();
            foreach ($data as $key => $one) {
                $_text[] = $key . ': ' . $one;
            }
            $log .= implode("\n", $_text);
        } else {
            $log .= 'null';
        }
        return $log . "\n";
    }

    /**
     * 日志应答内容
     * @param Array $data
     * @return String
     */
    private function response($data)
    {
        $log = "Response:[" . date("H:i:s") . "]\n";
        if (is_array($data)) {
            $log .= \Jason\Util\Json::array2JsonFormat($data);
        } else {
            $data = strval($data);
            $log .= $data;
        }
        $log .= "\nSQL:\n";
        $log .= \Jason\Util\Json::array2JsonFormat(\Jason\Middleware\Model::getQueryStringPool());
        return $log . "\n";
    }

}
