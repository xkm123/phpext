<?php
namespace php_ext\parser;

/**
 * Query解析
 * Class Query
 *
 * @package php_ext\parser
 */
class QueryParser
{
    /**
     * 将参数变为字符串
     *
     * @param array $array_query 参数集合
     *
     * @return string
     */
    public static function encode($array_query)
    {
        $tmp = array();
        foreach ($array_query as $k => $param) {
            $param = is_array($param) ? json_encode($param, JSON_UNESCAPED_UNICODE) : $param;
            $tmp[] = $k . '=' . $param;
        }
        $params = implode('&', $tmp);
        return $params;
    }

    /**
     * 转换query字符串成数组
     *
     * @param string $query 内容
     *
     * @return array|null
     */
    public static function decode($query)
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
            foreach ($params as $k => $v) {
                if (empty($k)) {
                    return null;
                }
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
}
