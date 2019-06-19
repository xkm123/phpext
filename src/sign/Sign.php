<?php
/**
 * Created by PhpStorm.
 * User: 邢可盟
 * Date: 2019/6/19
 * Time: 9:24
 */
namespace php_ext\sign;

use zkbr\library\sign\BaseSignAbstract;

/**
 * 签名工具
 * Class Sign
 * User: 邢可盟
 *
 * @package php_ext\sign
 */
class Sign extends BaseSignAbstract
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
    public static function sign($data, $sign_key, $sign_name = 'sign', $append_key_name = 'key')
    {
        $array_data = self::convertParams($data, $sign_name);
        $result = "";
        if (!is_null($array_data)) {
            $sort_data = self::sortArray($array_data);
            $result = self::convertQueryParams($sort_data, $sign_name);
        }
        $result .= $append_key_name . '=' . $sign_key;
        return self::md5Upper($result);
    }
}
