<?php

namespace Jason\Middleware;

class View
{

    /**
     * 
     */
    private static $instance;

    /**
     * @var \Twig_Environment
     */
    private $twig = null;

    /**
     * @var \Twig_Loader_Filesystem
     */
    private $fileLoader = null;
    private $fileName = null;
    private $_data = array();

    /**
     * 
     * @param type $fileName
     * @return \Jason\Middleware\View
     */
    public static function init($fileName)
    {
        if (!self::$instance) {
            self::$instance = new View($fileName);
        }
        self::$instance->fileName = $fileName . '.twig';
        return self::$instance;
    }

    public function __construct()
    {
        if (!$this->twig) {
            require_once 'View/Twig/Autoloader.php';
            \Twig_Autoloader::register(true);
            $this->fileLoader = new \Twig_Loader_Filesystem(\Jason\Jason::getConfig('APP_DIR') . DIRECTORY_SEPARATOR . 'View');
            $this->twig = new \Twig_Environment($this->fileLoader, array(
                'debug' => true
            ));
            $this->twig->addExtension(new \Twig_Extension_Debug());
        }
    }

    public function data($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    public function render()
    {
        return $this->twig->render($this->fileName, $this->_data);
    }

}
