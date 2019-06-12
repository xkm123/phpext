<?php
namespace PhpExt\logger\driver;

/**
 * Log委托类接口
 * Class LogInterface
 *
 * @package PhpExt\logger\driver
 */
interface LogInterface
{
    const LOG_ALL = "ALL";
    const LOG_LOG = "LOG";
    const LOG_DEBUG = "DEBUG";
    const LOG_INFO = "INFO";
    const LOG_NOTICE = "NOTICE";
    const LOG_WARNING = "WARNING";
    const LOG_ERROR = "ERROR";
    const LOG_CRITICAL = "CRITICAL";
    const LOG_ALERT = "ALERT";
    const LOG_EMERGENCY = "EMERGENCY";
    const LOG_DETAIL_ORDER_ASC = 1;
    const LOG_DETAIL_ORDER_DESC = 2;

    /**
     * 构造方法
     *
     * @param string $basePath 基础路径
     */
    public function __construct($basePath);

    /**
     * 设置basePath
     *
     * @param string $basePath 基础路径
     *
     * @return bool
     */
    public function setBasePath($basePath);

    /**
     * 设置默认模块名称
     *
     * @param string $module 设置默认模型名称
     *
     * @return bool
     */
    public function setDefaultLogger($module);

    /**
     * 设置DateTimeFormat配置
     *
     * @param $format
     *
     * @return bool
     */
    public function setDateTimeFormat($format);

    /**
     * 返回当前DateTimeFormat配置格式
     *
     * @return string
     */
    public function getDateTimeFormat();

    /**
     * 设置默认配置
     *
     * @param string $template 模板默认为"%T | %L | %P | %Q | %t | %M"
     *
     * @return bool
     */
    public function setDefaultTemplate($template);

    /**
     * 返回默认配置格式
     *
     * @return string
     */
    public function getDefaultTemplate();

    /**
     * 获取最后一次设置的模块目录
     *
     * @return string
     */
    public function getLastLogger();

    /**
     * 获取默认的模块目录
     *
     * @return string
     */
    public function getDefaultLogger();

    /**
     * 获取basePath
     *
     * @return string
     */
    public function getBasePath();

    /**
     * 记录debug日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function debug($message, array $context = array(), $module = '');

    /**
     * 记录info日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function info($message, array $context = array(), $module = '');

    /**
     * 记录notice日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function notice($message, array $context = array(), $module = '');

    /**
     * 记录warning日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function warning($message, array $context = array(), $module = '');

    /**
     * 记录error日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function error($message, array $context = array(), $module = '');

    /**
     * 记录critical日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function critical($message, array $context = array(), $module = '');

    /**
     * 记录alert日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function alert($message, array $context = array(), $module = '');

    /**
     * 记录emergency日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function emergency($message, array $context = array(), $module = '');

    /**
     * 通用日志方法
     *
     * @param string $level   级别
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function log($level, $message, array $context = array(), $module = '');

    /**
     * 设置请求Id
     *
     * @param string $request_id 请求id
     *
     * @return $this
     */
    public function setRequestID($request_id);

    /**
     * 获取请求的ID
     *
     * @return mixed
     */
    public function getRequestID();

    /**
     * 销毁
     */
    public function __destruct();

    /**
     * 统计所有类型（或单个类型）行数
     *
     * @param string $level    级别
     * @param string $log_path 日志文件名称
     * @param null   $key_word 关键字
     *
     * @return array | int
     */
    public function analyzerCount($level = self::LOG_ALL, $log_path = null, $key_word = null);

    /**
     * 以数组形式，快速取出某类型log的各行详情
     *
     * @param string $level    级别
     * @param string $log_path 日志文件名
     * @param null   $key_word 关键词
     * @param int    $start    开始偏移量 linux可用
     * @param int    $limit    数量 linux可用
     * @param int    $order    排序  linux可用
     *
     * @return array
     */
    public function analyzerDetail($level = self::LOG_ALL, $log_path = '*', $key_word = null, $start = 1,
        $limit = 20, $order = self::LOG_DETAIL_ORDER_ASC);
}
