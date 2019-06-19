<?php
namespace php_ext;

/**
 * 数组辅助类
 * Class Arr
 *
 * @package php_ext
 */
class Arr
{
    /**
     * 设置数组值
     *
     * @param array       $array 原始数组
     * @param string|null $key   数组key
     * @param mixed       $value 值
     * @param bool        $merge 是否合并  默认false
     *
     * @return mixed
     */
    public static function set(&$array, $key, $value, $merge = false)
    {
        if (is_null($key)) {
            return $array = $value;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $key = array_shift($keys);
        $array = self::arrayValueMerge($array, $key, $value, $merge);
        return $array;
    }

    /**
     * 获取数组值
     *
     * @param array       $array 原始数组
     * @param string|null $key   数组key
     *
     * @return mixed
     */
    public static function get($array, $key)
    {
        $temp = $array;
        if (is_null($key)) {
            return [];
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($temp[$key])) {
                return [];
            }
            $temp = &$temp[$key];
        }
        try {
            $temp = $temp[array_shift($keys)];
        } catch (\Exception $e) {
            $temp = [];
        }
        return $temp;
    }

    /**
     * 判断数组key存不存在
     *
     * @param array       $array 原始数组
     * @param string|null $key   数组key
     *
     * @return bool
     */
    public static function exists($array, $key)
    {
        $temp = $array;
        if (is_null($key)) {
            return false;
        }
        $keys = explode('.', $key);
        while (count($keys) > 0) {
            $key = array_shift($keys);
            if (!isset($temp[$key])) {
                $temp = null;
                return false;
            }
            $temp = &$temp[$key];
        }
        $temp = null;
        return true;
    }

    /**
     * 数组合并
     *
     * @param array $arr1 被合并数组
     * @param array $arr2 合并的数组
     *
     * @return array
     */
    public static function merge($arr1, $arr2)
    {
        $temp = $arr1;
        foreach ($arr2 as $k => $v) {
            if (isset($temp[$k]) && is_array($temp[$k]) && is_array($v)) {
                $temp[$k] = self::merge($temp[$k], $v);
            } else {
                $temp[$k] = $v;
            }
        }
        return $temp;
    }

    /**
     * 数组值合并
     *
     * @param array  $array 需要被合并的数组
     * @param string $key   需要合并的数组key
     * @param mixed  $value 要合并的值
     * @param bool   $merge 是否合并还是替换
     *
     * @return array
     */
    private static function arrayValueMerge(&$array, $key, $value, $merge)
    {
        if ($merge && is_array($value)) {
            if (!isset($array[$key])) {
                $array[$key] = [];
            }
            $array[$key] = self::merge($array[$key], $value);
        } else {
            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * 移除参数
     *
     * @param array $params 需要检查的参数集合
     * @param array $rules  检查的规则
     * @param callable(array,string)|null $callback 检查规则回调 返回bool
     *
     * @return array
     */
    public static function remove($params, $rules, callable $callback = null)
    {
        $temp = $params;
        foreach ($rules as $v) {
            if (!is_null($callback) && $callback($temp, $v)) {
                unset($temp[$v]);
            } elseif (self::exists($temp, $v)) {
                unset($temp[$v]);
            }
        }
        return $temp;
    }

    /**
     * 生成树形结构
     *
     * @param array  $data   数据
     * @param string $idKey  ID键名
     * @param string $pidKey 父ID键名
     * @param int    $pid    父id
     *
     * @return  array
     */
    public static function tree($data, $idKey = 'id', $pidKey = 'pid', $pid = 0)
    {
        foreach ($data as $k => $v) {
            if ($v[$pidKey] == $pid) {
                if (method_exists($v, 'toArray')) {
                    array_push($data, $v->toArray());
                } else {
                    array_push($data, $v);
                }
                unset($data[$k]);
            }
        }
        foreach ($data as $k => $v) {
            $data[$k]['children'] = self::tree($data, $idKey, $pidKey, $v[$idKey]);
        }
        return $data;
    }
    /**
     * 过滤数据
     *
     * @param  array $params     请求参数
     * @param  array $whiteRules 白名单
     * @param array  $blackRules 黑名单
     *
     * @return array
     */
    public static function filter($params, $whiteRules, $blackRules = [])
    {
        if (empty($whiteRules) && empty($blackRules)) {
            return $params;
        }
        foreach ($params as $k => $v) {
            if (!in_array($k, $whiteRules) || in_array($k, $blackRules)) {
                unset($params[$k]);
            }
        }
        return $params;
    }
}