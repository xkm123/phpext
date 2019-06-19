<?php
namespace php_ext\encrypt;

/**
 * Base64Url安全加密解密
 * Class Base64
 *
 * @package php_ext\encrypt
 */
class Base64
{
    /**
     * URL base64解码
     * '-' -> '+'
     * '_' -> '/'
     * 字符串长度%4的余数，补'='
     *
     * @param string $string
     *
     * @return string
     */
    public static function base64Decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    /**
     * URL base64编码
     * '+' -> '-'
     * '/' -> '_'
     * '=' -> ''
     *
     * @param string $string
     *
     * @return string
     */
    public static function base64Encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }
}
