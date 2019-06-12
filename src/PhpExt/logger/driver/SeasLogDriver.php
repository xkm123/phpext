<?php
namespace PhpExt\logger\driver;

use SeasLog;

/**
 * SeasLog驱动
 * Class SeasLogDriver
 *
 * @package PhpExt\logger
 */
class SeasLogDriver implements LogInterface
{
    /**
     * @var SeasLog
     */
    private $logger;

    /**
     * 构造方法
     *
     * @param string $basePath 基础路径
     */
    public function __construct($basePath)
    {
        $this->logger = new \SeasLog();
        $this->setBasePath($basePath);
    }

    /**
     * 设置basePath
     *
     * @param string $basePath 基础路径
     *
     * @return bool
     */
    public function setBasePath($basePath)
    {
        return $this->logger->setBasePath($basePath);
    }

    /**
     * 设置默认模块名称
     *
     * @param string $module 设置默认模型名称
     *
     * @return bool
     */
    public function setDefaultLogger($module)
    {
        return $this->logger->setLogger($module);
    }

    /**
     * 设置DateTimeFormat配置
     *
     * @param $format
     *
     * @return bool
     */
    public function setDateTimeFormat($format)
    {
        return $this->logger->setDatetimeFormat($format);
    }

    /**
     * 返回当前DateTimeFormat配置格式
     *
     * @return string
     */
    public function getDateTimeFormat()
    {
        return $this->logger->getDatetimeFormat();
    }

    /**
     * 统计所有类型（或单个类型）行数
     *
     * @param string $level    级别
     * @param string $log_path 日志文件名称
     * @param null   $key_word 关键字
     *
     * @return array | int
     */
    public function analyzerCount($level = self::LOG_ALL, $log_path = null, $key_word = null)
    {
        if (empty($log_path) && empty($key_word)) {
            return $this->logger->analyzerCount($level);
        } elseif ($level == self::LOG_ALL && empty($log_path) && !empty($key_word)) {
            return $this->logger->analyzerCount($key_word);
        } elseif (empty($log_path)) {
            $log_path = date('Ymd') . '.log';
        }
        return $this->logger->analyzerCount($level, $log_path, $key_word);
    }

    /**
     * 获取最后一次设置的模块目录
     *
     * @return string
     */
    public function getLastLogger()
    {
        return $this->logger->getLastLogger();
    }

    /**
     * 获取basePath
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->logger->getBasePath();
    }

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
        $limit = 20, $order = self::LOG_DETAIL_ORDER_ASC)
    {
        if (IS_WIN) {
            return $this->logger->analyzerDetail($level, $log_path, $key_word);
        }
        return $this->logger->analyzerDetail($level, $log_path, $key_word, $start, $limit, $order);
    }

    /**
     * 获得当前日志buffer中的内容
     *
     * @return array
     */
    public function getBuffer()
    {
        return $this->logger->getBuffer();
    }

    /**
     * 将buffer中的日志立刻刷到硬盘
     *
     * @return $this
     */
    public function flushBuffer()
    {
        $this->logger->flushBuffer();
        return $this;
    }

    /**
     * 记录debug日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function debug($message, array $context = array(), $module = '')
    {
        $context = self::interpolate($context);
        $this->logger->debug($message, $context, $module);
        return $this;
    }

    /**
     * 记录info日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function info($message, array $context = array(), $module = '')
    {
        $context = self::interpolate($context);
        $this->logger->info($message, $context, $module);
        return $this;
    }

    /**
     * 记录notice日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function notice($message, array $context = array(), $module = '')
    {
        $context = self::interpolate($context);
        $this->logger->notice($message, $context, $module);
        return $this;
    }

    /**
     * 记录warning日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function warning($message, array $context = array(), $module = '')
    {
        $context = self::interpolate($context);
        $this->logger->warning($message, $context, $module);
        return $this;
    }

    /**
     * 记录error日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function error($message, array $context = array(), $module = '')
    {
        $context = self::interpolate($context);
        $this->logger->error($message, $context, $module);
        return $this;
    }

    /**
     * 记录critical日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function critical($message, array $context = array(), $module = '')
    {
        $context = self::interpolate($context);
        $this->logger->critical($message, $context, $module);
        return $this;
    }

    /**
     * 记录alert日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function alert($message, array $context = array(), $module = '')
    {
        $context = self::interpolate($context);
        $this->logger->alert($message, $context, $module);
        return $this;
    }

    /**
     * 记录emergency日志
     *
     * @param string $message 消息
     * @param array  $context 上下文
     * @param string $module  模型
     *
     * @return $this
     */
    public function emergency($message, array $context = array(), $module = '')
    {
        $context = self::interpolate($context);
        $this->logger->emergency($message, $context, $module);
        return $this;
    }

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
    public function log($level, $message, array $context = array(), $module = '')
    {
        $context = self::interpolate($context);
        if (function_exists($level)) {
            $this->logger->$level($message, $context, $module);
        } else {
            $this->logger->log($message, $context, $module);
        }
        return $this;
    }

    /**
     * 设置请求Id
     *
     * @param string $request_id 请求id
     *
     * @return $this
     */
    public function setRequestID($request_id)
    {
        $this->logger->setRequestID($request_id);
        return $this;
    }

    /**
     * 获取请求的ID
     *
     * @return mixed
     */
    public function getRequestID()
    {
        return $this->logger->getRequestID();
    }

    /**
     * 销毁
     */
    public function __destruct()
    {
        $this->logger->__destruct();
    }

    /**
     * 用上下文信息替换记录信息中的占位符
     *
     * @param array $context 数据
     *
     * @return array  修改占位符KEY后的数据信息。
     */
    static public function interpolate(array $context)
    {
        // 构建一个花括号包含的键名的替换数组
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        // 替换记录信息中的占位符，最后返回修改后的记录信息。
        return $replace;
    }

    /**
     * 获取默认的模块目录
     *
     * @return string
     */
    public function getDefaultLogger()
    {
        return $this->logger->getLastLogger();
    }

    /**
     * 设置默认配置
     *
     * @param $template
     *
     * @return bool
     */
    public function setDefaultTemplate($template)
    {
        return true;
    }

    /**
     * 返回默认配置格式
     *
     * @return string
     */
    public function getDefaultTemplate()
    {
        return "";
    }
}
