<?php /** @noinspection PhpLanguageLevelInspection */
namespace php_ext\logger;

use php_ext\logger\driver\LogInterface;

/**
 * 日志类
 * Class Logger
 *
 * @package php_ext\logger
 */
class Logger
{
    /**
     * 默认日志类型
     */
    const LOGGER_TYPE_DEFAULT = 0;
    /**
     * 自定义日志类型
     */
    const LOGGER_TYPE_CUSTOM = 1;
    /**
     * 自定义转换换行日志类型
     */
    const LOGGER_TYPE_CUSTOM_NEWLINE = 2;
    /**
     * All level.
     */
    const ALL = -2147483647;
    /**
     * Detailed debug information.
     */
    const DEBUG = 100;
    /**
     * Interesting events.
     * Examples: User logs in, SQL logs.
     */
    const INFO = 200;
    /**
     * Uncommon events.
     */
    const NOTICE = 250;
    /**
     * Exceptional occurrences that are not errors.
     * Examples: Use of deprecated APIs, poor use of an API,
     * undesirable things that are not necessarily wrong.
     */
    const WARNING = 300;
    /**
     * Runtime errors.
     */
    const ERROR = 400;
    /**
     * Critical conditions.
     * Example: Application component unavailable, unexpected exception.
     */
    const CRITICAL = 500;
    /**
     * Action must be taken immediately.
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     */
    const ALERT = 550;
    /**
     * Urgent alert.
     */
    const EMERGENCY = 600;
    /**
     * 请求级别
     */
    private $requestLevel = self::ALL;
    /**
     * Logging levels from syslog protocol defined in RFC 5424.
     * This is a static variable and not a constant to serve as an extension point for custom levels
     *
     * @var string[] Logging levels with the levels as key
     */
    protected static $levels = [
        self::DEBUG => 'debug',
        self::INFO => 'info',
        self::NOTICE => 'notice',
        self::WARNING => 'warning',
        self::ERROR => 'error',
        self::CRITICAL => 'critical',
        self::ALERT => 'alert',
        self::EMERGENCY => 'emergency',
    ];
    /**
     * 默认模型
     *
     * @var string
     */
    private $defaultLoggerModel = 'default';
    /**
     * 默认模型
     *
     * @var string
     */
    private $tempModel;
    /**
     * 默认的日志类型
     *
     * @var int
     */
    private $defaultLoggerType = self::LOGGER_TYPE_CUSTOM;
    /**
     * tag标识
     *
     * @var string 标识
     */
    private $tag = '标识';
    /**
     * 调用函数数量
     *
     * @var int
     */
    private $functionNum = 3;
    /**
     * Logger对象
     *
     * @var LogInterface
     */
    private $logger;
    /**
     * 配置信息
     *
     * @var array
     */
    private $config = [
        //基础路径
        'base_path' => '/log/www',
        //默认模型
        'default_model' => 'default',
        //时间格式
        'datetime_format' => 'Y-m-d H:i:s',
        //日志格式模板 默认"%T | %L | %P | %Q | %t | %M"
        'default_template' => "%T | %L | %P | %Q | %t | %M",
        //日志驱动
        'driver' => 'FileDriver',
        //日志级别  DEBUG,INFO,NOTICE,WARNING,ERROR,CRITICAL,ALERT,EMERGENCY,ALL
        'level' => 'ALL',
        //调用栈打印开关
        'trace_debug' => false
    ];

    /**
     * 构造参数
     *
     * @param array $config 配置信息
     *
     * @return Logger
     */
    public function __construct(array $config = array())
    {
        $this->config = array_merge($this->config, $config);
        $type = $this->config['driver'];
        $class = false !== strpos($type, '\\') ? $type : '\\php_ext\\logger\\driver\\' . ucwords($type);
        $this->logger = new $class($this->config['base_path']);
        $this->logger->setDefaultTemplate($this->config['default_template']);
        $this->logger->setDateTimeFormat($this->config['datetime_format']);
        $this->defaultLoggerModel = $this->config['default_model'];
        $this->switchModel($this->config['default_model']);
        $this->setRequestLevel($this->config['level']);
        return $this;
    }

    /**
     * 设置配置
     *
     * @param array $config 配置信息
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * 获取配置信息
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * 获取日志驱动
     *
     * @return LogInterface
     */
    public function getLoggerDriver()
    {
        return $this->logger;
    }

    /**
     * 切换日志类型
     *
     * @param int $type 类型
     *
     * @return $this
     */
    public function switchLoggerType($type)
    {
        $this->defaultLoggerType = $type;
        return $this;
    }

    /**
     * 获取日志类型
     *
     * @return int
     */
    public function getLoggerType()
    {
        return $this->defaultLoggerType;
    }

    /**
     * 将dump出来的html格式保留
     *
     * @param mixed $obj 需要打印的对象
     */
    static public function dumpToHtml($obj)
    {
        echo "<pre><xmp>";
        var_dump($obj);
        echo "</xmp></pre>";
    }

    /**
     * 获取指定模型
     *
     * @param array  $config     配置信息
     * @param string $model_name 模型名称
     *
     * @return Logger
     */
    static public function model(array $config, $model_name)
    {
        return self::instance($config)->switchModel($model_name);
    }

    /**
     * 设置头信息
     *
     * @param string $tag         标识
     * @param int    $loggerType  日志类型
     * @param int    $functionNum 调用函数数量
     *
     * @return $this
     */
    public function setHeader($tag, $loggerType = self::LOGGER_TYPE_DEFAULT, $functionNum = 3)
    {
        $this->tag = $tag;
        $this->functionNum = $functionNum;
        $this->defaultLoggerType = $loggerType;
        return $this;
    }

    /**
     * 初始化
     *
     * @param array $config 配置信息
     *
     * @return Logger
     */
    static public function instance(array $config = array())
    {
        return new self($config);
    }

    /**
     * 设置本次请求标识.
     *
     * @param string $request_id 设置本次的请求标识
     *
     * @return $this
     */
    public function setRequestID($request_id)
    {
        $this->logger->setRequestID($request_id);
        return $this;
    }

    /**
     * 获取本次请求标识.
     *
     * @return string
     */
    public function getRequestID()
    {
        return $this->logger->getRequestID();
    }

    /**
     * 设置默认的模块名称.
     *
     * @param string $module 默认的模块名称
     *
     * @return $this
     */
    public function setDefaultLogger($module)
    {
        $this->defaultLoggerModel = $module;
        $this->logger->setDefaultLogger($module);
        return $this;
    }

    /**
     * 获取默认的模块名称.
     *
     * @return string
     */
    public function getDefaultLogger()
    {
        return $this->defaultLoggerModel;
    }

    /**
     * 设置basePath
     *
     * @param string $basePath 基础路径
     *
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->logger->setBasePath($basePath);
        return $this;
    }

    /**
     * 获取基础路径
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->logger->getBasePath();
    }

    /**
     * 获取当前选择的模型
     *
     * @return string
     */
    public function getSwitchModel()
    {
        return $this->tempModel;
    }

    /**
     * 设置请求级别 只针对log(level有限制)
     *
     * @param int|string $level 请求级别
     *
     * @return $this
     */
    public function setRequestLevel($level = self::ALL)
    {
        $this->requestLevel = $this->getIntLevel($level);
        return $this;
    }

    /**
     * emergency日志
     *
     * @param mixed $message 消息
     * @param array $context 上下文
     *
     * @return $this
     */
    public function emergency($message, array $context = [])
    {
        if (self::EMERGENCY < $this->requestLevel) {
            return $this;
        }
        $message = $this->interpolate($message);
        $this->logger->emergency($message, $context, $this->tempModel);
        return $this;
    }

    /**
     * 重置参数
     *
     * @return $this
     */
    public function reset()
    {
        $this->defaultLoggerType = self::LOGGER_TYPE_DEFAULT;
        return $this->switchModel(null);
    }

    /**
     * alert日志
     *
     * @param mixed $message 消息
     * @param array $context 上下文
     *
     * @return $this
     */
    public function alert($message, array $context = [])
    {
        if (self::ALERT < $this->requestLevel) {
            return $this;
        }
        $message = $this->interpolate($message);
        $this->logger->alert($message, $context, $this->tempModel);
        return $this;
    }

    /**
     * critical日志
     *
     * @param mixed $message 消息
     * @param array $context 上下文
     *
     * @return $this
     */
    public function critical($message, array $context = [])
    {
        if (self::CRITICAL < $this->requestLevel) {
            return $this;
        }
        $message = $this->interpolate($message);
        $this->logger->critical($message, $context, $this->tempModel);
        return $this;
    }

    /**
     * error日志
     *
     * @param mixed $message 消息
     * @param array $context 上下文
     *
     * @return $this
     */
    public function error($message, array $context = [])
    {
        if (self::ERROR < $this->requestLevel) {
            return $this;
        }
        $message = $this->interpolate($message);
        $this->logger->error($message, $context, $this->tempModel);
        return $this;
    }

    /**
     * warning日志
     *
     * @param mixed $message 消息
     * @param array $context 上下文
     *
     * @return $this
     */
    public function warning($message, array $context = [])
    {
        if (self::WARNING < $this->requestLevel) {
            return $this;
        }
        $message = $this->interpolate($message);
        $this->logger->warning($message, $context, $this->tempModel);
        return $this;
    }

    /**
     * notice日志
     *
     * @param mixed $message 消息
     * @param array $context 上下文
     *
     * @return $this
     */
    public function notice($message, array $context = [])
    {
        if (self::NOTICE < $this->requestLevel) {
            return $this;
        }
        $message = $this->interpolate($message);
        $this->logger->notice($message, $context, $this->tempModel);
        return $this;
    }

    /**
     * info日志
     *
     * @param mixed $message 消息
     * @param array $context 上下文
     *
     * @return $this
     */
    public function info($message, array $context = [])
    {
        if (self::INFO < $this->requestLevel) {
            return $this;
        }
        $message = $this->interpolate($message);
        $this->logger->info($message, $context, $this->tempModel);
        return $this;
    }

    /**
     * debug日志
     *
     * @param mixed $message 消息
     * @param array $context 上下文
     *
     * @return $this
     */
    public function debug($message, array $context = [])
    {
        if (self::DEBUG < $this->requestLevel) {
            return $this;
        }
        $message = $this->interpolate($message);
        $this->logger->debug($message, $context, $this->tempModel);
        return $this;
    }

    /**
     * log日志
     *
     * @param string|int $level   级别
     * @param mixed      $message 消息
     * @param array      $context 上下文
     *
     * @return $this
     */
    public function log($level, $message, array $context = [])
    {
        $level = $this->getIntLevel($level);
        if ((int)$level < $this->requestLevel) {
            return $this;
        }
        if (!array_key_exists($level, self::$levels)) {
            return $this;
        }
        $levelFunction = strtolower(self::$levels[$level]);
        $this->logger->$levelFunction($message, $context, $this->tempModel);
        return $this;
    }

    /**
     * 获取int级别
     *
     * @param int|string $level 级别
     *
     * @return mixed
     */
    private function getIntLevel($level)
    {
        if (is_string($level)) {
            $level = strtolower($level);
            $levels = array_flip(self::$levels);
            if (isset($levels[$level])) {
                return $levels[$level];
            }
            return self::ALL;
        }
        return $level;
    }

    /**
     * 切换模型
     *
     * @param null $model 模型名称
     *
     * @return $this
     */
    public function switchModel($model = null)
    {
        if (empty($model)) {
            $model = $this->defaultLoggerModel;
        }
        $this->tempModel = $model;
        $this->logger->setDefaultLogger($this->tempModel);
        return $this;
    }

    /**
     * 获取最后一次设置的模块目录.
     *
     * @return string
     */
    public function getLastLogger()
    {
        return $this->logger->getLastLogger();
    }

    /**
     * 替换记录信息中的换行
     *
     * @param string $content 数据
     *
     * @return string
     */
    private function interpolate($content)
    {
        $content = self::dumpLogToString($content);
        switch ($this->defaultLoggerType) {
            case self::LOGGER_TYPE_CUSTOM:
                $temp = $this->tag . ' | ' . $content;
                return self::convertNewLine($temp, 2);
            case self::LOGGER_TYPE_CUSTOM_NEWLINE:
                $temp = $this->tag . ' | ' . $content;
                return self::convertNewLine($temp, 0);
            default:
                $temp = $this->tag . ' | <h5>日志信息：</h5>' . $content;
                if ($this->config['trace_debug']) {
                    if (is_int($this->functionNum) && $this->functionNum > 0 || is_array($this->functionNum)) {
                        $temp .= $this->getCallFunctions($this->functionNum);
                    }
                }
                return self::convertNewLine($temp);
        }
    }

    /**
     * 替换记录信息中的换行
     *
     * @param string $content 数据
     * @param int    $type    0:转成html<br/> 1:转成\r\n 2:转成‘’
     *
     * @return string
     */
    static public function convertNewLine($content, $type = 0)
    {
        $temp = $content;
        if ($type == 0) {
            $temp = str_replace("\r\n", "<br/>", $temp);
            return str_replace("\n", "<br/>", $temp);
        } elseif ($type == 1) {
            return str_replace("<br/>", "\r\n", $temp);
        } else {
            return str_replace("\r\n", "", $temp);
        }
    }

    /**
     * 将dump出来的字符串保存到变量中
     *
     * @param mixed $expression
     *
     * @return string
     */
    public static function dumpToString($expression)
    {
        ob_start();
        var_dump($expression);
        $var = ob_get_clean();
        $var = str_replace("\n", "\r\n", $var);
        return $var;
    }

    /**
     * 将dump出来的字符串保存到变量中
     *
     * @param mixed $expression
     *
     * @return string
     */
    private static function dumpLogToString($expression)
    {
        if (!is_null($expression) && ($expression instanceof \Exception || $expression instanceof \Throwable)) {
            $expression = ['file' => $expression->getFile(), 'line' => $expression->getLine(),
                'msg' => $expression->getMessage()];
        }
        if (!is_string($expression) && !is_numeric($expression) && !is_bool($expression)) {
            ob_start();
            var_dump($expression);
            $var = ob_get_clean();
            $var = str_replace("\n", "\r\n", $var);
            return $var;
        }
        return $expression;
    }

    /**
     * 统计所有类型（或单个类型）行数.
     *
     * @param string $level    级别
     * @param string $log_path 日志文件名称
     * @param null   $key_word 关键字
     *
     * @return array
     */
    public function analyzerCount($level = LogInterface:: LOG_ALL, $log_path = null, $key_word = null)
    {
        return $this->logger->analyzerCount($level, $log_path, $key_word);
    }

    /**
     * 以数组形式，快速取出某类型log的各行详情.
     *
     * @param string $level    级别
     * @param string $log_path 日志路径
     * @param null   $key_word 关键词
     * @param int    $start    开始
     * @param int    $limit    多少条
     * @param int    $order    默认为正序 LOG_DETAIL_ORDER_ASC，可选倒序 LOG_DETAIL_ORDER_DESC
     *
     * @return array
     */
    public function analyzerDetail(
        $level = LogInterface:: LOG_ALL,
        $log_path = '*',
        $key_word = null,
        $start = 1,
        $limit = 20,
        $order = LogInterface:: LOG_DETAIL_ORDER_DESC
    )
    {
        return $this->logger->analyzerDetail($level, $log_path, $key_word, $start, $limit, $order);
    }

    /**
     * 获取调用函数的过程
     *
     * @param int|array $function_num 函数数量
     *
     * @return string
     */
    private function getCallFunctions($function_num)
    {
        $debugInfo = debug_backtrace();
        $functionNum = [2, 3];
        if (is_int($function_num)) {
            $functionNum[1] = $function_num;
        } elseif (is_array($function_num) && count($function_num) > 1) {
            $functionNum[0] += $function_num[0];
            $functionNum[1] = $function_num[1];
        }
        if (count($debugInfo) < $functionNum[0]) {
            return '';
        }
        $stack = "<h5>调用过程：</h5>[<br/>";
        $k = 1;
        for ($i = $functionNum[0]; $i < count($debugInfo); $i++) {
            if ($functionNum[1] <= 0) {
                break;
            }
            $stack .= "   (" . ($k++) . ") { file:" . (isset($debugInfo[$i]["file"]) ? $debugInfo[$i]["file"] : 'null');
            $stack .= ",line:" . (isset($debugInfo[$i]["line"]) ? $debugInfo[$i]["line"] : 'null');
            $stack .= ",function:" . (isset($debugInfo[$i]["function"]) ? $debugInfo[$i]["function"] : 'null') . " }<br/>";
            $functionNum[1]--;
        }
        $stack .= "]<br/>";
        return $stack;
    }
}