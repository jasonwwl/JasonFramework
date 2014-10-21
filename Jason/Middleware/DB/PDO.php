<?php

namespace Jason\Middleware\DB;

/**
 * 数据库驱动中间件
 * 目前只实现了PDO方式操作MySQL数据库的驱动
 * 
 * @package Jason
 * @author  Jason Wang <jasonwx@163.com>
 */
class PDO
{

    /**
     * PHP自带的PDO的实例
     * @var \PDO
     */
    public $instance;

    /**
     * PDOStatement的实例
     * @var \PDOStatement
     */
    public $stmt;

    /**
     * 生命周期里所有SQL请求按顺序存入该容器中
     * @var Array 
     */
    public $queryStringPool;

    /**
     * 初始化PDO驱动
     * @param Array $config
     */
    public function __construct($config)
    {
        $dsn = "mysql:host=" . $config['HOST'] . ";dbname=" . $config['DBNAME'];
        $pdoConfig = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        );
        $this->instance = new \PDO($dsn, $config['USERNAME'], $config['PASSWORD'], $pdoConfig);
        $this->instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * 执行SQL
     * @param string $sql
     * @param array $arr execute对应的数组键值对
     */
    public function exec($sql, $arr = null)
    {
        $rb = null;
        if (is_array($arr)) {
            foreach ($arr as $key => $one) {
                $rb[] = $key . ' -> ' . $one;
            }
        }
        $this->queryStringPool[] = array(
            'SQL' => $sql,
            'PARAMS' => $rb
        );
        $this->stmt = $this->instance->prepare($sql);
        $res = $this->stmt->execute($arr);
        return $res;
    }

    /**
     * 返回的结果集进行遍历封装成Array
     * @param Boolean $one 是否只取第一行结果
     * @return Array 封装后的结果集
     */
    public function fetch($one = false)
    {
        $result = false;
        while ($row = $this->stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($one) {
                $result = $row;
                break;
            } else {
                $result[] = $row;
            }
        }
        return $result;
    }

}
