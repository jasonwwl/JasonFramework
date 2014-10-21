<?php

namespace Jason\Middleware;

use Jason\Middleware\DB\PDO;

/**
 * Jason框架的数据模型层中间件
 * 
 * @package Jason
 * @author  Jason Wang <jasonwx@163.com>
 */
class Model
{

    /**
     * 数据库中间件驱动的实例
     * 
     * @var \Jason\Middleware\DB\PDO
     */
    private static $driver;

    /**
     * 获取驱动的实例
     * @return \Jason\Middleware\DB\PDO;
     */
    private static function getInstance()
    {
        if (!self::$driver) {
            self::$driver = new PDO(\Jason\Jason::getConfig('DB'));
        }
        return self::$driver;
    }

    /**
     * 数据库查找
     * @param Array $arr 条件规则
     * @param Boolean $one 是否只返回第一条结果
     * @return Mixed/Boolean 返回的数据集,若为空则为false
     */
    protected static function select($arr, $one = false)
    {
        $pdo = self::getInstance();
        $parser = self::buildParser($arr);
        $pdo->exec($parser['sql'], $parser['val']);
        return $pdo->fetch($one);
    }

    /**
     * 执行一条插入语句
     * @param array $arr sql的规则
     * @return String/Boolean 若成功则为true或自增ID的值，否则为false
     */
    protected static function insert($arr)
    {
        $pdo = self::getInstance();
        self::beginTransaction();
        $parser = self::buildParser($arr);
        $res = $pdo->exec($parser['sql'], $parser['val']);
        $lnsid = $pdo->instance->lastInsertId();
        return intval($lnsid) > 0 ? $lnsid : $res;
    }

    /**
     * 执行一条“修改”语句（UPDATE/DELETE）
     * @param Array $arr sql的规则
     * @return Boolean 是否成功
     */
    protected static function change($arr)
    {
        $pdo = self::getInstance();
        self::beginTransaction();
        $parser = self::buildParser($arr);
        $res = $pdo->exec($parser['sql'], $parser['val']);
        return $res;
        if ($pdo->stmt->rowCount() > 0) {
            return true;
        } else {
            throw new \PDOException('change err', '800100');
        }
    }

    /**
     * 事务开始
     * @return boolean
     */
    public static function beginTransaction()
    {
        if (self::inTransaction()) {
            return false;
        } else {
            self::getInstance()->queryStringPool[] = '[BEGIN]Transaction';
            return self::getInstance()->instance->beginTransaction();
        }
    }

    /**
     * 事务是否已经开启
     * @return Boolean
     */
    private static function inTransaction()
    {
        return self::getInstance()->instance->inTransaction();
    }

    /**
     * 提交事务
     * @return boolean
     */
    public static function commit()
    {
        if (self::inTransaction()) {
            self::getInstance()->queryStringPool[] = '[COMMIT]Transaction';
            return self::getInstance()->instance->commit();
        } else {
            return false;
        }
    }

    /**
     * 事务回滚
     * @return boolean
     */
    public static function rollback()
    {
        if (self::inTransaction()) {
            self::getInstance()->queryStringPool[] = '[ROLLBACK]Transaction';
            return self::getInstance()->instance->rollBack();
        } else {
            return false;
        }
    }

    /**
     * 获取生命周期内所执行的SQL
     * @return Array
     */
    public static function getQueryStringPool()
    {
        return self::getInstance()->queryStringPool;
    }

    /**
     * 创建供stsm利用的SQL格式
     * @param Array $arr
     * @return Array
     */
    private static function buildParser($arr)
    {
        $sql = $arr[0];
        $val = null;
        if (isset($arr[1])) {
            foreach ($arr[1] as $key => $v) {
                if (substr($key, 0, 1) !== ":") {
                    $key = ':' . $key;
                }
                $val[$key] = $v;
            }
        }
        return array(
            'sql' => $sql,
            'val' => $val
        );
    }

    /**
     * 生成供PDO prepare使用的数据，只可生成update语句
     * 
     * @param type $tableName 表名
     * @param type $pkName where名
     * @param type $params 键值对
     * @return type
     */
    public static function buildUpdateSqlArr($tableName, $pkName, $params)
    {
        if ($pkName) {
            $where = " WHERE `{$pkName}` = :{$pkName} ";
        } else {
            $where = null;
        }
        $update = "UPDATE `{$tableName}` ";
        $set = "SET";
        $_set = null;
        foreach ($params as $key => $one) {
            if ($key == $pkName) {
                continue;
            }
            $_set[] = " `{$key}` = :{$key}";
        }
        $set .= implode(',', $_set);
        $sql = $update . $set . $where;
        return array($sql, $params);
    }

    public static function buildInsertSqlArr($tableName, $params)
    {
//        $sql = "INSERT INTO `question_im` 
//        (`quesiton_id`, `user_id`, `question_id`, `from`, `content`, `chat_time`,`model_type`,`model_id`) 
//        VALUES (:chat_id, :user_id, :question_id, :from, :content, :chat_time, :model_type, :model_id)";
        $insert = "INSERT INTO `{$tableName}` ";
        $nameList = null;
        $valueList = null;
        foreach ($params as $key => $one) {
            $nameList[] = "`{$key}`";
            $valueList[] = ":{$key}";
        }
        $nameList = implode(', ', $nameList);
        $valueList = implode(', ', $valueList);
        $sql = $insert . "({$nameList}) VALUES ({$valueList})";
        return array($sql, $params);
    }

}
