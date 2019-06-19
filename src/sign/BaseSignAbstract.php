<?php
namespace zkbr\library\sign;

/**
 * 签名基类
 * Class BaseSignAbstract
 *
 * @package zkbr\library\sign
 */
abstract class BaseSignAbstract
{
    /**
     * 签名
     *
     * @param string|array $data            需要签名的数据
     * @param string       $sign_key        签名key
     * @param string       $sign_name       签名字段名称
     * @param string       $append_key_name 拼接key名称
     *
     * @return string
     */
    abstract public static function sign($data, $sign_key, $sign_name = 'sign', $append_key_name = 'key');

    /**
     * 返回大写的MD5
     *
     * @param string $content
     *
     * @return string
     */
    protected static function md5Upper($content)
    {
        return strtoupper(MD5($content));
    }

    /**
     * 返回小写的MD5
     *
     * @param string $content
     *
     * @return string
     */
    protected static function md5Lower($content)
    {
        return strtolower(MD5($content));
    }

    /**
     * 转key/value
     *
     * @param array  $array     须签名的数组
     * @param string $sign_name 签名字段名称
     *
     * @return string
     */
    protected static function convertQueryParams($array, $sign_name = 'sign')
    {
        $content = "";
        if (is_array($array)) {//判断是不是数组
            foreach ($array as $key => $value) {
                if (self::checkFilterParams($key, $value, $sign_name)) {
                    $content .= $key . "=" . $value . "&";
                }
            }
        }
        return $content;
    }

    /**
     * 检查过滤参数
     *
     * @param string $key       字段key
     * @param mixed  $value     字段值
     * @param string $sign_name 签名字段名称
     *
     * @return bool
     */
    protected static function checkFilterParams($key, $value, $sign_name = 'sign')
    {
        if ($key == $sign_name || is_array($value) || is_null($value)) {
            return false;
        }
        if (!is_numeric($value) && !is_string($value) && !is_bool($value)) {
            return false;
        }
        if (is_string($value) && ($value === '' || strtolower($value) === 'null')) {
            return false;
        }
        return true;
    }

    /**
     * 解析复杂的json数组转化为url参数
     *
     * @param array  $array     须签名的数组
     * @param string $content   追加的字符串对象
     * @param string $sign_name 签名字段名称
     *
     * @return string
     */
    protected static function convertArrayQueryParams($array, &$content, $sign_name = 'sign')
    {
        if (is_array($array)) {//判断是不是数组
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    self::convertArrayQueryParams($value, $content, $sign_name);
                } elseif (self::checkFilterParams($key, $value, $sign_name)) {
                    $content .= $key . "=" . $value . "&";
                }
            }
        }
        return $content;
    }

    /**
     * 转换参数
     *
     * @param string|array $data      需要转换的参数
     * @param string       $sign_name 签名字段名称
     *
     * @return array|mixed
     */
    protected static function convertParams($data, $sign_name = 'sign')
    {
        if (!is_array($data)) {
            $tempJson = "";
            if (!is_string($data)) {
                $tempJson = json_encode($data, JSON_UNESCAPED_UNICODE);
            }
            $jsonArray = json_decode($tempJson, true);
        } else {
            $jsonArray = $data;
        }
        if (isset($jsonArray[$sign_name])) {
            unset($jsonArray[$sign_name]);
        }
        return $jsonArray;
    }

    /**
     * 保留key排序方法
     *
     * @param array $arr    需要排序的数组
     * @param bool  $is_asc 升序：true 降序：false
     * @param bool  $is_key 是否key排序
     *
     * @return array
     */
    protected static function sortArray($arr, $is_asc = true, $is_key = true)
    {
        $new_array = self::sortKey($arr, $is_asc, $is_key);
        $new_sort = array();
        foreach ($new_array as $k => $v) {
            foreach ($arr as $key => $value) {
                if (($is_key && $v == $key) || (!$is_key && $v == $value)) {
                    $new_sort[$key] = $value;
                    unset($arr[$key]);
                    break;
                }
            }
        }
        return $new_sort;
    }

    /**
     * 排序key
     *
     * @param array $arr    数组
     * @param bool  $is_asc 是否升序
     * @param bool  $is_key 是否key排序
     *
     * @return array 排序后的数组
     */
    protected static function sortKey($arr, $is_asc, $is_key)
    {
        $new_array = array();
        foreach ($arr as $key => $value) {
            if ($is_key) {
                $new_array[] = $key;
            } else {
                $new_array[] = $value;
            }
        }
        if ($is_asc) { //asc
            asort($new_array);
        } else {//desc
            arsort($new_array);
        }
        return $new_array;
    }
}
