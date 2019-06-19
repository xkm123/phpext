<?php
namespace php_ext\func;

/**
 * 函数驱动接口类
 * Class FunctionDriverInterface
 *
 * @package php_ext\func
 */
interface FunctionDriverInterface
{
    /**
     * 开启事务
     */
    public static function startTrans();

    /**
     * 事务回滚
     */
    public static function rollback();

    /**
     * 事务提交
     */
    public static function commit();

    /**
     * 写入日志
     *
     * @param string|mixed $msg   消息
     * @param string       $level 级别
     * @param null|string  $tag   标识
     * @param null|string  $model 模型名
     */
    public static function log($msg, $level = 'debug', $tag = null, $model = null);
}
