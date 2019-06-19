<?php
namespace php_ext;

use php_ext\parser\XmlParser;

/**
 * 字符串工具
 * Class Str
 *
 * @package php_ext
 */
class Str
{
    protected static $snakeCache = [];
    protected static $camelCache = [];
    protected static $studlyCache = [];

    /**
     * 检查字符串中是否包含某些字符串
     *
     * @param string       $haystack
     * @param string|array $needles
     *
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
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
     * 检查字符串是否以某些字符串结尾
     *
     * @param  string       $haystack
     * @param  string|array $needles
     *
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ((string)$needle === static::substr($haystack, -static::length($needle))) {
                return true;
            }
        }
        return false;
    }

    /**
     * 检查字符串是否以某些字符串开头
     *
     * @param  string       $haystack
     * @param  string|array $needles
     *
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && mb_strpos($haystack, $needle) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取指定长度的随机字母数字组合的字符串
     *
     * @param  int $length
     *
     * @return string
     */
    public static function random($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return static::substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    /**
     * 字符串转小写
     *
     * @param  string $value
     *
     * @return string
     */
    public static function lower($value)
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * 字符串转大写
     *
     * @param  string $value
     *
     * @return string
     */
    public static function upper($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * 获取字符串的长度
     *
     * @param  string $value
     *
     * @return int
     */
    public static function length($value)
    {
        return mb_strlen($value);
    }

    /**
     * 截取字符串
     *
     * @param  string   $string
     * @param  int      $start
     * @param  int|null $length
     *
     * @return string
     */
    public static function subStr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * 驼峰转下划线
     *
     * @param  string $value
     * @param  string $delimiter
     *
     * @return string
     */
    public static function snake($value, $delimiter = '_')
    {
        $key = $value;
        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', $value);
            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        }
        return static::$snakeCache[$key][$delimiter] = $value;
    }

    /**
     * 下划线转驼峰(首字母小写)
     *
     * @param  string $value
     *
     * @return string
     */
    public static function camel($value)
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }
        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * 下划线转驼峰(首字母大写)
     *
     * @param  string $value
     *
     * @return string
     */
    public static function studly($value)
    {
        $key = $value;
        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

    /**
     * 转为首字母大写的标题格式
     *
     * @param  string $value
     *
     * @return string
     */
    public static function title($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * 去除空格
     *
     * @param string $str 需要去除空格的字符串
     *
     * @return mixed
     */
    public static function trimAll($str)
    {
        return preg_replace('# #', '', $str);
    }

    /**
     * 去除Sql特殊字符
     *
     * @param string $str 需要去除的字符串
     *
     * @return mixed
     */
    public static function trimSqlParam($str)
    {
        $str = preg_replace('# #', '', $str);
        $str = preg_replace('[\$]', '', $str);
        return $str;
    }

    /**
     * 生成UUID 单机使用
     *
     * @access public
     * @return string
     */
    static public function uuid()
    {
        $charid = md5(uniqid(mt_rand(), true));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            . substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12)
            . chr(125);// "}"
        return $uuid;
    }

    /**
     * 生成Guid主键
     *
     * @return Boolean
     */
    static public function keyGen()
    {
        return str_replace('-', '', substr(self::uuid(), 1, -1));
    }

    /**
     * 检查字符串是否是UTF8编码
     *
     * @param string $string 字符串
     *
     * @return Boolean
     */
    static public function isUtf8($str)
    {
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($str[$i]);
            if ($c > 128) {
                if (($c >= 254)) {
                    return false;
                } elseif ($c >= 252) {
                    $bits = 6;
                } elseif ($c >= 248) {
                    $bits = 5;
                } elseif ($c >= 240) {
                    $bits = 4;
                } elseif ($c >= 224) {
                    $bits = 3;
                } elseif ($c >= 192) {
                    $bits = 2;
                } else {
                    return false;
                }
                if (($i + $bits) > $len) {
                    return false;
                }
                while ($bits > 1) {
                    $i++;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191) {
                        return false;
                    }
                    $bits--;
                }
            }
        }
        return true;
    }

    /**
     * 字符串截取，支持中文和其他编码
     *
     * @static
     * @access public
     *
     * @param string $str     需要转换的字符串
     * @param string $start   开始位置
     * @param string $length  截取长度
     * @param string $charset 编码格式
     * @param string $suffix  截断显示字符
     *
     * @return string
     */
    static public function mSubStr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
    {
        if (function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $charset);
        } elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $charset);
        } else {
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice . '...' : $slice;
    }

    /**
     * 获取一定范围内的随机数字 位数不足补零
     *
     * @param integer $min 最小值
     * @param integer $max 最大值
     *
     * @return string
     */
    static public function randNumber($min, $max)
    {
        return sprintf("%0" . strlen($max) . "d", mt_rand($min, $max));
    }

    /**
     * 自动转换字符集 支持数组转换
     *
     * @param string $string 需要转换的字符串
     * @param string $from   当前格式
     * @param string $to     转换后格式
     *
     * @return array|string
     */
    static public function autoCharset($string, $from = 'gbk', $to = 'utf-8')
    {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        if (strtoupper($from) === strtoupper($to) || empty($string) || (is_scalar($string) && !is_string($string))) {
            //如果编码相同或者非字符串标量则不转换
            return $string;
        }
        if (is_string($string)) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($string, $to, $from);
            } elseif (function_exists('iconv')) {
                return iconv($from, $to, $string);
            } else {
                return $string;
            }
        } elseif (is_array($string)) {
            foreach ($string as $key => $val) {
                $_key = self::autoCharset($key, $from, $to);
                $string[$_key] = self::autoCharset($val, $from, $to);
                if ($key != $_key) {
                    unset($string[$key]);
                }
            }
            return $string;
        } else {
            return $string;
        }
    }

    /**
     * 解析参数
     *
     * @param string $data 传过来的字符串
     *
     * @return array|mixed|null
     */
    public static function parseParam($data)
    {
        if (empty($data)) {
            return [];
        }
        $result = json_decode($data, true);
        if (!$result) {
            $result = self::convertUrlQuery($data);
            if (!$result) {
                try {
                    $result = XmlParser::decode($data);
                } catch (\Exception $e1) {
                    return null;
                }
            }
        }
        return $result;
    }

    /**
     * 将字符串参数变为数组
     *
     * @param string $query 查询对象
     *
     * @return array
     */
    public static function convertUrlQuery($query)
    {
        try {
            $params = array();
            if (strpos($query, '&') !== false) {
                $queryParts = explode('&', $query);
                foreach ($queryParts as $param) {
                    $index = strpos($param, '=');
                    $key = substr($param, 0, $index);
                    $value = substr($param, $index + 1, strlen($param) - $index);
                    $params[$key] = self::decodeUnicode(urldecode($value));
                }
            } else {
                $index = strpos($query, '=');
                $key = substr($query, 0, $index);
                $value = substr($query, $index + 1, strlen($query) - $index);
                $params[$key] = self::decodeUnicode(urldecode($value));
            }
            return $params;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 将字符串中的中文转义字符转化为中文
     *
     * @param string $str 含有转义的字符
     *
     * @return mixed 返回中文的字符
     */
    public static function decodeUnicode($str)
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function ($matches) {
            return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");
        }, $str);
    }

    /**
     * 判断是否是简单的jsonObject对象
     *
     * @param string $json json字符串
     *
     * @return bool true：简单的json对象 如：{a:1,b:2} false:复杂的json对象 如：[{},{}] ,{a:[{},{}],b:{},c:1}
     */
    public static function isSimpleJson($json)
    {
        $count = substr_count($json, '{');
        $count2 = substr_count($json, '[');
        //判断是不是简单的json
        if ($count == 1 && $count2 == 0) {
            return true;
        }
        return false;
    }

    /**
     * 是否数据库字段重复
     *
     * @param string $str 需检测的字符串
     *
     * @return bool
     */
    public static function isDuplicateEntry($str)
    {
        $status = strpos($str, 'Duplicate entry');
        return !$status ? false : true;
    }

    /**
     * 去掉文件中的 bom头
     *
     * @param string $contents 内容
     *
     * @return mixed
     */
    public static function clearBom($contents)
    {
        //UTF8 去掉文本中的 bom头
        $BOM = chr(239) . chr(187) . chr(191);
        return str_replace($BOM, '', $contents);
    }

    /**
     * 验证密码复杂度
     *
     * @param string $candidate  需要校验的字符串
     * @param int    $passMinLen 密码最小长度
     * @param int    $level      级别  <0：普通级别 1：中等级别 >1：严格校验
     *
     * @return true|string true：复杂度检测成功 string：错误信息
     */
    public static function validPass($candidate, $passMinLen = 6, $level = 0)
    {
        $condition = ['/[0-9]/', '/[a-z]/', '/[A-Z]/', '/[~!@#$%^&*()\-_=+{};:<,.>?]/'];
        if (strlen($candidate) < $passMinLen) {
            return "密码必须包含至少含有" . $passMinLen . "个字符";
        }
        if (preg_match_all($condition[1], $candidate, $o) < 1 &&
            preg_match_all($condition[2], $candidate, $o) < 1) {
            return "密码必须包含至少一个小写字母或大写字母";
        }
        if (preg_match_all($condition[0], $candidate, $o) < 1) {
            return "密码必须包含至少一个数字";
        }
        if ($level > 0) {
            if (preg_match_all($condition[1], $candidate, $o) < 1 ||
                preg_match_all($condition[2], $candidate, $o) < 1) {
                return "密码必须包含至少一个小写字母和大写字母";
            }
        }
        if ($level > 1) {
            if (preg_match_all($condition[3], $candidate, $o) < 1) {
                return "密码必须包含至少一个特殊符号：[~!@#$%^&*()\-_=+{};:<,.>?]";
            }
        }
        return true;
    }

    /**
     * 转换一个String字符串为byte数组
     *
     * @param string $str   需要转换的字符串
     * @param array  $bytes 目标byte数组
     *
     * @return array
     */
    public static function getBytes($string)
    {
        $bytes = array();
        for ($i = 0; $i < strlen($string); $i++) {
            $bytes[] = ord($string[$i]);
        }
        return $bytes;
    }

    /**
     * 移除末尾字符串
     *
     * @param string $str  需要检查的字符串
     * @param string $rule 需要移除的字符串
     *
     * @return string
     */
    public static function rTrim($str, $rule)
    {
        $strLen = strlen($str);
        $ruleLen = strlen($rule);
        if ($strLen < $ruleLen) {
            return $str;
        }
        $temp = substr($str, $strLen - $ruleLen, $ruleLen);
        if ($temp == $rule) {
            return substr($str, 0, $strLen - $ruleLen);
        }
        return $str;
    }

    /**
     * 移除开头字符串
     *
     * @param string $str  需要检查的字符串
     * @param string $rule 需要移除的字符串
     *
     * @return string
     */
    public static function lTrim($str, $rule)
    {
        $strLen = strlen($str);
        $ruleLen = strlen($rule);
        if ($strLen < $ruleLen) {
            return $str;
        }
        $temp = substr($str, 0, $ruleLen);
        if ($temp == $rule) {
            return substr($str, $ruleLen, $strLen - $ruleLen);
        }
        return $str;
    }
}
