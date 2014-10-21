<?php

namespace Jason;

/**
 * Jason框架的预加载、引导程序
 *
 * @package Jason
 * @author  Jason Wang <jasonwx@163.com>
 */
class Init
{

    /**
     * 主框架实例的容器
     * @var \Jason\Jason
     */
    private static $jasonInstance;

    public function __construct($config = null)
    {
        if (is_null(self::$jasonInstance)) {
            require 'Jason.php';
            self::$jasonInstance = new \Jason\Jason($config);
        }
    }

    /**
     * 运行主框架
     */
    public function run()
    {
        self::$jasonInstance->run();
    }

    /**
     * 获取主框架的实例
     *
     * @return \Jason\Jason;
     */
    public static function getJason()
    {
        return self::$jasonInstance;
    }

}
