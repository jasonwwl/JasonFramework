<?php

namespace Jason\Http;

/**
 * HTTP 请求内容处理
 *
 * @package Jason
 * @author  Jason Wang <jasonwx@163.com>
 */
class Request
{

    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_OVERRIDE = '_METHOD';

    protected $env;

    /**
     * 初始化注入过来的Jason主类
     * @param \Jason\Jason $app
     */
    public function __construct(\Jason\Jason $app)
    {
        $this->env = $app->env;
    }

    /**
     * Fetch GET data
     *
     * This method returns a key-value array of data sent in the HTTP request query string, or
     * the value of the array key if requested; if the array key does not exist, NULL is returned.
     *
     * @param  string $key
     * @param  mixed $default Default return value when key does not exist
     * @return array|mixed|null
     */
    public function get($key = null, $default = null)
    {
        if (!isset($this->env['jason.request.query_hash'])) {
            $output = array();
            parse_str($this->env['QUERY_STRING'], $output);
            $this->env['jason.request.query_hash'] = Util::stripSlashesIfMagicQuotes($output);
        }
        if ($key) {
            if (isset($this->env['jason.request.query_hash'][$key])) {
                return $this->env['jason.request.query_hash'][$key];
            } else {
                return $default;
            }
        } else {
            return $this->env['jason.request.query_hash'];
        }
    }

    /**
     * Fetch POST data
     *
     * This method returns a key-value array of data sent in the HTTP request body, or
     * the value of a hash key if requested; if the array key does not exist, NULL is returned.
     *
     * @param  string $key
     * @param  mixed $default Default return value when key does not exist
     * @return array|mixed|null
     * @throws \RuntimeException If environment input is not available
     */
    public function post($key = null, $default = null)
    {
        if (!isset($this->env['jason.input'])) {
            throw new \RuntimeException('Missing jason.input in environment variables');
        }
        if (!isset($this->env['jason.request.form_hash'])) {
            $this->env['jason.request.form_hash'] = array();
            if (is_array($_json = @json_decode($this->env['jason.input'], true))) {
                $this->env['jason.request.form_hash'] = Util::stripSlashesIfMagicQuotes($_json);
            } elseif (is_string($this->env['jason.input'])) {
                $output = array();
                parse_str($this->env['jason.input'], $output);
                $this->env['jason.request.form_hash'] = Util::stripSlashesIfMagicQuotes($output);
            } else {
                $this->env['jason.request.form_hash'] = Util::stripSlashesIfMagicQuotes($_POST);
            }
        }
        if ($key) {
            if (isset($this->env['jason.request.form_hash'][$key])) {
                return $this->env['jason.request.form_hash'][$key];
            } else {
                return $default;
            }
        } else {
            return $this->env['jason.request.form_hash'];
        }
    }

    /**
     * Fetch GET and POST data
     *
     * This method returns a union of GET and POST data as a key-value array, or the value
     * of the array key if requested; if the array key does not exist, NULL is returned,
     * unless there is a default value specified.
     *
     * @param  string $key
     * @param  mixed $default
     * @return array|mixed|null
     */
    public function params($key = null, $default = null)
    {
        $union = array_merge($this->get(), $this->post());
        if ($key) {
            return isset($union[$key]) ? $union[$key] : $default;
        }
        $data = array();
        foreach ($union as $key => $val) {
            if ($key == 'HEADER_HTTP_REP' || $key == 'HEADER_HTTP_TOKEN' || $key == 'HEADER_HTTP_SIGN') {
                continue;
            }
            $data[$key] = $val;
        }
        return $data;
    }

    /**
     * Get HTTP method
     * @return string
     */
    public function getMethod()
    {
        return $this->env['REQUEST_METHOD'];
    }

    /**
     * Is this a GET request?
     * @return bool
     */
    public function isGet()
    {
        return $this->getMethod() === self::METHOD_GET;
    }

    /**
     * Is this a POST request?
     * @return bool
     */
    public function isPost()
    {
        return $this->getMethod() === self::METHOD_POST;
    }

    /**
     * Is this a PUT request?
     * @return bool
     */
    public function isPut()
    {
        return $this->getMethod() === self::METHOD_PUT;
    }

    /**
     * Is this a PATCH request?
     * @return bool
     */
    public function isPatch()
    {
        return $this->getMethod() === self::METHOD_PATCH;
    }

    /**
     * Is this a DELETE request?
     * @return bool
     */
    public function isDelete()
    {
        return $this->getMethod() === self::METHOD_DELETE;
    }

    /**
     * Is this a HEAD request?
     * @return bool
     */
    public function isHead()
    {
        return $this->getMethod() === self::METHOD_HEAD;
    }

    /**
     * Is this a OPTIONS request?
     * @return bool
     */
    public function isOptions()
    {
        return $this->getMethod() === self::METHOD_OPTIONS;
    }

}
