<?php
/**
 * Created by PhpStorm.
 * User: 623279281@qq.com
 * Date: 2019/6/19
 * Time: 9:38
 */
namespace php_ext;

use php_ext\parser\FormDataParser;
use php_ext\parser\JsonParser;
use php_ext\parser\QueryParser;
use php_ext\parser\XmlParser;

/**
 * 辅助类
 * Class Helper
 * User: 623279281@qq.com
 *
 * @package php_ext
 */
class Helper
{
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
     * 将dump出来的html格式保留
     *
     * @param mixed $obj 需要打印的对象
     */
    public static function dumpToHtml($obj)
    {
        echo "<pre><xmp>";
        var_dump($obj);
        echo "</xmp></pre>";
    }

    /**
     * 解析内容
     *
     * @param string $content 请求内容文本
     *
     * @return array|null
     */
    public static function parseParam($content)
    {
        if (empty($content)) {
            return null;
        } else {
            $result = JsonParser::decode($content);
            if (!$result) {
                $result = FormDataParser::decode($content);
                if (!$result) {
                    $result = XmlParser::decode($content);
                    if (!$result) {
                        $result = QueryParser::decode($content);
                    }
                }
            }
            return $result;
        }
    }

    /**
     * 检测设备系统
     *
     * @return string
     */
    public static function device()
    {
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if (stristr($agent, 'iPad')) {
            $fb_fs = "iPad";
        } elseif (preg_match('/Android (([0-9_.]{1,3})+)/i', $agent, $version)) {
            $fb_fs = "Android";
        } elseif (stristr($agent, 'Linux')) {
            $fb_fs = "Linux";
        } elseif (preg_match('/iPhone OS (([0-9_.]{1,3})+)/i', $agent, $version)) {
            $fb_fs = "iPhone";
        } elseif (preg_match('/Mac OS X (([0-9_.]{1,5})+)/i', $agent, $version)) {
            $fb_fs = "Mac";
        } elseif (preg_match('/unix/i', $agent)) {
            $fb_fs = "Unix";
        } elseif (preg_match('/windows/i', $agent) && preg_match('/nt 6.1/i', $agent)) {
            $fb_fs = "Windows 7";
        } elseif (preg_match('/windows/i', $agent) && preg_match('/nt 6.2/i', $agent)) {
            $fb_fs = "Windows 8";
        } elseif (preg_match('/windows/i', $agent) && preg_match('/nt 10.0/i', $agent)) {
            $fb_fs = "Windows 10";
        } else {
            $fb_fs = "Unknown";
        }
        return $fb_fs;
    }

    /**
     * /**
     * 产生随机字符串
     *
     * @param int  $length 最小20位
     * @param bool $isRandomDate
     *
     * @return string
     * @throws \Exception
     */
    public static function getNonceStr($length = 32, $isRandomDate = false)
    {
        if ($length < 20) {
            throw new \Exception("长度不能小于20位");
        }
        $chars = array('0', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q',
            'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        if ($isRandomDate) {
            $date = str_split(date('YmdHis') . self::getMillisecond());//20180308025823893  17位
        } else {
            $date = str_split(date('YmdHis') . self::getMillisecond());//20180308025823893  17位
        }
        $str = "";
        foreach ($date as $k => $v) {
            $str .= $chars[$v];
        }
        for ($i = 0; $i < $length - 17; $i++) {
            $str .= $chars[mt_rand(0, count($chars) - 1)];
        }
        return $str;
    }

    /**
     * 获取订单号字符串
     *
     * @param int    $length   长度
     * @param string $startStr 开始字符串  最多4位
     *
     * @return string
     * @throws \Exception
     */
    public static function getOrderNum($length = 20, $startStr = '')
    {
        $startStr = empty($startStr) ? "" : $startStr;
        if ($length < 20 || strlen($startStr) > 4) {
            throw new \Exception("长度不能小于20位或起始字符长度不能超过4位");
        }
        $date = date('ymdHis') . self::getMillisecond();//180308025823893  15位
        $str = $startStr . $date;
        $chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $len = $length - 15 - strlen($startStr);
        for ($i = 0; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, count($chars) - 1)];
        }
        return $str;
    }

    /**
     * microsecond 微秒     millisecond 毫秒
     * 返回时间戳的毫秒数部分
     *
     * @return float
     */
    public static function getMillisecond()
    {
        $a = microtime();
        $msec = substr($a . "", 2, 3);
        return $msec;
    }

    /**
     * 资源api调用
     * 使用：return CommonTool::toRestfulApi($this, __FUNCTION__, param...);
     * 注：1.存在：调用方法 (get,post,put,delete,patch)+函数名称  如：getIndex()
     *     2.不存在：调用方法 miss()
     *
     * @param string|\stdClass $class       类名
     * @param string           $action_name 方法名称
     *
     * @return mixed
     */
    public static function toRestfulApi($class, $action_name)
    {
        $params = func_get_args();
        unset($params[0]);
        unset($params[1]);
        $params = array_values($params);
        $method = $_SERVER['REQUEST_METHOD'];
        $toMethod = strtolower($method) . ucwords($action_name);
        try {
            if (is_string($class)) {
                $class = new $class();
            }
            return call_user_func_array(array($class, $toMethod), $params);
        } catch (\Exception $e) {
            array_unshift($params, $toMethod);
            return call_user_func_array(array($class, 'miss'), $params);
        }
    }

    /**
     * 判断是否为空
     *
     * @param mixed $data
     *
     * @return bool
     */
    public static function isEmpty($data)
    {
        if (is_null($data)) {
            return true;
        }
        if (is_string($data) && $data === '') {
            return true;
        }
        if (is_array($data) && count($data) == 0) {
            return true;
        }
        return false;
    }
}
