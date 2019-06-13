<?php
namespace php_ext\parser;

/**
 * Json解析
 * Class JsonParser
 *
 * @package php_ext\parser
 */
class JsonParser
{
    /**
     * 对数据进行 json_encode  中文不转义 float double类型修复精度
     *
     * @param mixed $data     数据
     * @param int   $decimals 给浮点数定精度
     * @param int   $depth    遍历的深度
     *
     * @return string
     */
    public static function encode($data, $decimals = 0, $depth = 0)
    {
        $data = self::prepare($data, $decimals, $depth);
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 对数据进行遍历，修复浮点数的精度
     *
     * @param mixed $data     数据
     * @param int   $decimals 给浮点数定精度
     * @param int   $depth    遍历的深度
     * @param int   $level    当前深度
     *
     * @return mixed $data
     */
    public static function prepare($data, $decimals = 0, $depth = 0, $level = 0)
    {
        $depth = $depth >= 0 ? $depth : 128;
        if ($level > $depth - 1) {
            return $data;
        }
        if (is_array($data)) {
            foreach ($data as $i => $v) {
                $data[$i] = self::prepare($v, $decimals, $depth, $level + 1);
            }
            return $data;
        }
        if (is_float($data)) {
            return self::fixNumberPrecision($data, $decimals);
        }
        return $data;
    }

    /**
     * 修复浮点数精度
     *
     * @param float $number   数字
     * @param int   $decimals 精度
     *
     * @return float
     */
    public static function fixNumberPrecision($number, $decimals = 0)
    {
        $decimals = $decimals >= 0 ? $decimals : 8;
        $formatted = number_format($number, $decimals, '.', '');
        return floatval($formatted);
    }

    /**
     * 解析json字符串转数组
     *
     * @param string $str json字符串
     *
     * @return mixed
     */
    public static function decode($str)
    {
        return json_decode($str, true);
    }
}
