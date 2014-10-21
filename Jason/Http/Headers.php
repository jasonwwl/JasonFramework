<?php

namespace Jason\Http;

/**
 * HTTP 请求头处理
 *
 * @package Jason
 * @author  Jason Wang <jasonwx@163.com>
 */
class Headers
{

    protected static $special = array(
        'CONTENT_TYPE',
        'CONTENT_LENGTH',
        'PHP_AUTH_USER',
        'PHP_AUTH_PW',
        'PHP_AUTH_DIGEST',
        'AUTH_TYPE'
    );
    private static $header;
    /**
     * @var \Jason\Jason
     */
    private $jason;
    private $env;

    /**
     * Extract HTTP headers from an array of data (e.g. $_SERVER)
     * @param  array $data
     * @return array
     */
    public static function extract($data)
    {
        if (is_null(self::$header)) {
            $results = array();
            foreach ($data as $key => $value) {
                $key = strtoupper($key);
                if (strpos($key, 'X_') === 0 || strpos($key, 'HTTP_') === 0 || in_array($key, static::$special)) {
                    if ($key === 'HTTP_CONTENT_LENGTH') {
                        continue;
                    }
                    $results[$key] = $value;
                }
            }
            self::$header = $results;
        }
        return self::$header;
    }

    /**
     * 初始化注入过来的Jason主类
     * @param \Jason\Jason $app
     */
    public function __construct(\Jason\Jason $app)
    {
        $this->env = $app->env;
        $this->jason = $app;
        self::extract($this->env);
    }

    /**
     * Get Nbsf API Token
     * @return string
     */
    public function getToken()
    {
        if (isset(self::$header['HTTP_TOKEN'])) {
            return self::$header['HTTP_TOKEN'];
        } elseif ($this->jason->request->params('HEADER_HTTP_TOKEN')) {
            return $this->jason->request->params('HEADER_HTTP_TOKEN');
        }
        return null;
    }

    /**
     * Get Nbsf API Replay code
     * @return string
     */
    public function getRep()
    {
        if (isset(self::$header['HTTP_REP'])) {
            return self::$header['HTTP_REP'];
        } elseif ($this->jason->request->params('HEADER_HTTP_REP')) {
            return $this->jason->request->params('HEADER_HTTP_REP');
        }
        return null;
    }

    /**
     * Get Nbsf API Signstr
     * @return string
     */
    public function getSign()
    {
        if (isset(self::$header['HTTP_SIGN'])) {
            return self::$header['HTTP_SIGN'];
        } elseif ($this->jason->request->params('HEADER_HTTP_SIGN')) {
            return urlencode($this->jason->request->params('HEADER_HTTP_SIGN'));
        }
        return null;
    }

    /**
     * GET USERAGENT
     * @return string
     */
    public function getAgent()
    {
        return isset(self::$header['HTTP_USER_AGENT']) ? self::$header['HTTP_USER_AGENT'] : null;
    }

    /*     * ******************************************************************************
     * Instance interface
     * ***************************************************************************** */

    /**
     * Transform header name into canonical form
     * @param  string $key
     * @return string
     */
    protected function normalizeKey($key)
    {
        $key = strtolower($key);
        $key = str_replace(array('-', '_'), ' ', $key);
        $key = preg_replace('#^http #', '', $key);
        $key = ucwords($key);
        $key = str_replace(' ', '-', $key);

        return $key;
    }

}
