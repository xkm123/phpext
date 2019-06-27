<?php
namespace php_ext\logger\driver;

class FileDriver implements LogInterface
{
    /**
     * 基础路径
     *
     * @var string
     */
    private $basePath = '/log/www';
    /**
     * 模型名称
     *
     * @var string
     */
    private $module = 'default';
    /**
     * 时间格式
     *
     * @var string
     */
    private $dateFormat = 'Y-m-d H:i:s';
    /**
     * 最后的模型名称
     *
     * @var string
     */
    private $lastModule = 'default';
    /**
     * 模板
     *
     * @var string
     */
    private $temple = "%T | %L | %P | %Q | %t | %M";
    /**
     * 请求id
     *
     * @var string
     */
    private $requestID = '';

    /**
     * 构造方法
     *
     * @param string $basePath 基础路径
     */
    public function __construct($basePath)
    {
        $this->setBasePath($basePath);
        $this->setDateTimeFormat(null);
        $this->setDefaultLogger(null);
        $this->setDefaultTemplate(null);
        $this->lastModle = $this->module;
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
        $this->basePath = $basePath;
        return true;
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
        if (!empty($module)) {
            $this->module = $module;
        }
        return true;
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
        if (!empty($format)) {
            $this->dateFormat = $format;
        }
        return true;
    }

    /**
     * 返回当前DateTimeFormat配置格式
     *
     * @return string
     */
    public function getDateTimeFormat()
    {
        return $this->dateFormat;
    }

    /**
     * 获取最后一次设置的模块目录
     *
     * @return string
     */
    public function getLastLogger()
    {
        return $this->lastModule;
    }

    /**
     * 获取basePath
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
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
        $this->log("debug", $message, $context, $module);
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
        $this->log("info", $message, $context, $module);
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
        $this->log("notice", $message, $context, $module);
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
        $this->log("warning", $message, $context, $module);
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
        $this->log("error", $message, $context, $module);
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
        $this->log("critical", $message, $context, $module);
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
        $this->log("alert", $message, $context, $module);
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
        $this->log("emergency", $message, $context, $module);
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
        if (empty($this->requestID)) {
            $this->requestID = self::guid();
        }
        $module = empty($module) ? $this->module : $module;
        $this->lastModle = $module;
        $context = self::interpolate($context);
        if (!function_exists($level)) {
            $level = "log";
        }
        foreach ($context as $k => $v) {
            $message = self::replace($message, $k, $v);
        }
        $this->writeMessage($module, strtoupper($level), $message);
        return $this;
    }

    /**
     * 写消息
     *
     * @param string $module  模型
     * @param string $level   级别
     * @param string $message 消息
     *
     * @return bool
     */
    private function writeMessage($module, $level, $message)
    {
        if (php_sapi_name() == 'cli' && strpos($module, '_cli') > 0) {
            $module .= '_cli';
        }
        $result = self::replace($this->temple, "%T", date($this->dateFormat, time()));
        $result = self::replace($result, "%L", $level);
        $result = self::replace($result, "%P", 0);
        $result = self::replace($result, "%Q", $this->requestID);
        $result = self::replace($result, "%t", time());
        $result = self::replace($result, "%M", $message);
        $dirPath = $this->basePath . DIRECTORY_SEPARATOR . $module;
        self::createDir($dirPath);
        $fileName = $dirPath . DIRECTORY_SEPARATOR . date("Ymd", time()) . ".log";
        $this->requestID = '';
        return error_log($result . "\n", 3, $fileName);
    }

    /**
     * 创建多级目录
     *
     * @param string $dir  文件夹路径
     * @param int    $mode 权限
     *
     * @return boolean
     */
    public static function createDir($dir, $mode = 0777)
    {
        return is_dir($dir) or (self::createDir(dirname($dir)) and mkdir($dir, $mode));
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
        $this->requestID = empty($request_id) ? self::guid() : $request_id;
        return $this;
    }

    /**
     * 生成uuid
     *
     * @return   string      [uuid] 长度36
     */
    public static function guid()
    {
        $uuid = '';
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            //$uuid = //chr(123)// "{"
            $uuid .= substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
            //.chr(125);// "}"
            return $uuid;
        }
    }

    /**
     * 获取请求的ID
     *
     * @return mixed
     */
    public function getRequestID()
    {
        return $this->requestID;
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
     * 销毁
     */
    public function __destruct()
    {

    }

    /**
     * 获取默认的模块目录
     *
     * @return string
     */
    public function getDefaultLogger()
    {
        return $this->module;
    }

    /**
     * 设置默认配置
     *
     * @param $format
     *
     * @return bool
     */
    public function setDefaultTemplate($format)
    {
        if (!empty($format)) {
            $this->temple = $format;
        }
        return true;
    }

    /**
     * 替换字符串
     *
     * @param string $str     被替换的字符串
     * @param string $search  需要替换的字符串
     * @param string $replace 要替换的字符串
     *
     * @return mixed
     */
    public static function replace($str, $search, $replace)
    {
        return str_replace($search, $replace, $str);
    }

    /**
     * 返回默认配置格式
     *
     * @return string
     */
    public function getDefaultTemplate()
    {
        return $this->temple;
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
        $path = $this->basePath . '/' . $this->module . '/';
        if (!empty($log_path) && intval($log_path)) {
            $path .= trim($log_path) . '.log';
        } else {
            $path .= date('Ymd', time()) . '.log';
        }
        if ($level != self::LOG_ALL) {
            $key_word = " | " . $level . " | ";
        }
        $result = $this->readFileByLine($path, $key_word, $start, $limit, $order);
        if (is_string($result)) {
            return [];
        } else {
            return $result;
        }
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
        return $level == self::LOG_ALL ? [] : 0;
    }

    /**
     * 读取文件
     *
     * @param string $fileName 文件名称
     * @param string $key      关键字
     * @param int    $start    起始行
     * @param int    $limit    数量
     * @param bool   $revers   是否反向
     *
     * @return array|string
     */
    public function readFileByLine($fileName, $key = "", $start = 0, $limit = 20, $revers = false)
    {
        if ($limit <= 0) {
            return array();
        }
        $start = $start < 0 ? 0 : $start;
        if (!$revers) {
            return $this->readFileAsc($fileName, $key, $start, $limit);
        } else {
            return $this->readFileDesc($fileName, $key, $start, $limit);
        }
    }

    /**
     * 正序读取文件
     *
     * @param string $fileName 文件名称
     * @param string $key      关键字
     * @param int    $start    起始行
     * @param int    $limit    数量
     *
     * @return array|string
     */
    private function readFileAsc($fileName, $key = "", $start = 0, $limit = 20)
    {
        $result = array();
        if (!is_file($fileName) || !$fp = fopen($fileName, 'r')) {
            return "打开文件失败，请检查文件路径是否正确：" . $fileName;
        }
        $curLine = 0;
        while ($limit > 0 && !feof($fp) && $content = fgets($fp)) {
            $curLine++;
            if ($curLine >= $start && !empty(trim($content))) {
                if (empty($key)) {
                    array_push($result, $content);
                    $limit--;
                } elseif (mb_strpos($content, $key) !== false) {
                    array_push($result, $content);
                    $limit--;
                }
            }
        }
        fclose($fp);
        return $result;
    }

    /**
     * 倒叙读取文件
     *
     * @param string $fileName 文件名称
     * @param string $key      关键字
     * @param int    $start    起始行
     * @param int    $limit    数量
     *
     * @return array|string
     */
    private function readFileDesc($fileName, $key = "", $start = 0, $limit = 20)
    {
        $result = array();
        if (!is_file($fileName) || !$fp = fopen($fileName, 'r')) {
            return "打开文件失败，请检查文件路径是否正确：" . $fileName;
        }
        $tempParams = ['cur_line' => 0, 'pos' => -2, 'eof' => ''];
        //输出文本中所有的行，直到文件结束为止。
        while ($limit > 0 && !feof($fp)) {
            while ($tempParams['eof'] != "\n") {//这里控制从文件的最后一行开始读
                if (!fseek($fp, $tempParams['pos'], SEEK_END)) {
                    $tempParams['eof'] = fgetc($fp);
                    $tempParams['pos']--;
                } else {
                    break;
                }
            }
            if (($content = fgets($fp)) == false) {
                fseek($fp, 0, SEEK_SET);
                if (($content = fgets($fp)) == false) {
                    break;
                }
                $limit = 0;
            }
            $tempParams['eof'] = "";
            $tempParams['cur_line']++;
            if (!empty(trim($content)) && $tempParams['cur_line'] >= $start) {
                if (empty($key)) {
                    array_push($result, $content);
                    $limit--;
                } elseif (mb_strpos($content, $key) !== false) {
                    array_push($result, $content);
                    $limit--;
                }
            }
        }
        fclose($fp);
        return $result;
    }
}
