<?php

require '../Jason/Init.php';

use Jason\Route as R;
use Jason\Http\Response as RP;

$config = array(
    'DEBUG' => false,
    'DB' => array(
        'USERNAME' => 'test',
        'PASSWORD' => 'xxx',
        'HOST' => '192.168.1.16',
        'DBNAME' => 'test'
    ),
    'LOG' => __DIR__ . DIRECTORY_SEPARATOR . '../log/Test/', //LOG文件地址
    'LOG_SAVE_REQUEST' => false,//是否保存日志
    'APP_DIR' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Test',
);
$jasonInit = new Jason\Init($config);

//路由
R::get('/', array('\\Test\\Controller\\Index\\Index', 'welcome'), RP::HEADER_HTML);

$jasonInit->run();
