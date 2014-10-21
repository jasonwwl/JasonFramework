<?php

namespace Jason\Middleware;

/**
 * Jason框架的控制层中间件
 * 
 * @package Jason
 * @author  Jason Wang <jasonwx@163.com>
 */
class Controller
{

    /**
     * 接收注入过来的Jason主类
     * @var \Jason\Jason 
     */
    protected $jason;

    /**
     * 实例化控制层时，将Jason框架主类注入进控制层
     * @param \Jason\Jason $app
     */
    public function __construct(\Jason\Jason $app)
    {
        $this->jason = $app;
    }

    /**
     * 将恶意调用的请求直接飞到defaultNotFound
     * @param type $name
     * @param type $arguments
     */
    public function __call($name, $arguments)
    {
        $this->jason->defaultNotFound();
    }

}
