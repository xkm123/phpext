<?php
namespace php_ext\config;

use php_ext\Arr;
use php_ext\config\interf\ConfigDriverInterface;

defined('CACHE_PATH') or define('CACHE_PATH', __DIR__ . '/../../../../runtime');
/**
 * 配置文件加载类
 * Class Config
 *
 * @package php_ext\config
 */
class Config
{
    /**
     * @var array 配置参数
     */
    private static $config = ['_sys_' => []];
    /**
     * @var string 参数作用域
     */
    private static $range = '_sys_';

    /**
     * 设定配置参数的作用域
     *
     * @access public
     *
     * @param  string $range 作用域
     *
     * @return void
     */
    public static function range($range)
    {
        self::$range = $range;
        if (!isset(self::$config[$range])) {
            self::$config[$range] = [];
        }
    }

    /**
     * 重置配置参数
     *
     * @access public
     *
     * @param  string $range 作用域
     *
     * @return void
     */
    public static function reset($range = '')
    {
        $range = $range ?: self::$range;
        if (true === $range) {
            self::$config = ['_sys_' => []];
        } else {
            self::$config[$range] = [];
        }
    }

    /**
     * 加载配置文件或内容
     *
     * @access public
     *
     * @param  string $config 配置文件路径或内容
     * @param  string $type   配置解析类型
     * @param  string $name   配置名（如设置即表示二级配置）
     * @param  string $range  作用域
     *
     * @return mixed
     * @throws \Exception
     */
    public static function load($config, $type = '', $name = '', $range = '')
    {
        $range = $range ?: self::$range;
        if (is_file($config)) {
            $type = pathinfo($config, PATHINFO_EXTENSION);
        } elseif (!is_string($config) || empty($type)) {
            return [];
        }
        $class = (false !== strpos($type, '\\')) ? $type : '\\php_ext\\config\\driver\\' . ucwords($type);
        /** @var ConfigDriverInterface $class */
        $class = new $class();
        return self::set($name, $class->parse($config), $range);
    }

    /**
     * 加载配置文件或内容
     *
     * @access public
     *
     * @param  string $config 配置文件路径或内容
     * @param  string $type   配置解析类型
     *
     * @return array
     * @throws \Exception
     */
    public static function parse($config, $type = '')
    {
        if (is_file($config)) {
            $type = pathinfo($config, PATHINFO_EXTENSION);
        } elseif (!is_string($config) || empty($type)) {
            return [];
        }
        $class = (false !== strpos($type, '\\')) ? $type : '\\php_ext\\config\\driver\\' . ucwords($type);
        /** @var ConfigDriverInterface $class */
        $class = new $class();
        return $class->parse($config);
    }

    /**
     * 检测配置是否存在
     *
     * @access public
     *
     * @param  string $name  配置参数名（支持二级配置 . 号分割）
     * @param  string $range 作用域
     *
     * @return bool
     */
    public static function has($name, $range = '')
    {
        $range = $range ?: self::$range;
        if (!isset(self::$config[$range])) {
            self::$config[$range] = [];
        }
        return Arr::exists(self::$config[$range], $name);
    }

    /**
     * 获取配置参数 为空则获取所有配置
     *
     * @access public
     *
     * @param  string $name  配置参数名（支持多级配置 . 号分割）
     * @param  string $range 作用域
     *
     * @return mixed
     */
    public static function get($name = null, $range = '')
    {
        $range = $range ?: self::$range;
        if (!isset(self::$config[$range])) {
            self::$config[$range] = [];
        }
        // 无参数时获取所有
        if (empty($name) && isset(self::$config[$range])) {
            return self::$config[$range];
        }
        // 非二级配置时直接返回
        if (!strpos($name, '.')) {
            $name = strtolower($name);
            return isset(self::$config[$range][$name]) ? self::$config[$range][$name] : null;
        }
        return Arr::get(self::$config[$range], $name);
    }

    /**
     * 设置配置参数 name 为数组则为批量设置
     *
     * @access public
     *
     * @param  string $name  配置参数名（支持多级配置 . 号分割）
     * @param  mixed  $value 配置值
     * @param  string $range 作用域
     *
     * @return mixed
     */
    public static function set($name, $value, $range = '')
    {
        $range = $range ?: self::$range;
        if (!isset(self::$config[$range])) {
            self::$config[$range] = [];
        }
        // 字符串则表示单个配置设置
        if (empty($name)) {
            Arr::set(self::$config, $range, $value, true);
        } else {
            Arr::set(self::$config[$range], $name, $value, true);
        }
        //返回配置
        return self::$config[$range];
    }
}
