<?php
namespace php_ext\config\driver;

use php_ext\config\interf\ConfigDriverInterface;

/**
 * 解析INI配置文件
 * Class Ini
 *
 * @package php_ext\config\driver
 */
class Ini implements ConfigDriverInterface
{
    /**
     * 解析
     *
     * @param string $config 配置文件或内容
     *
     * @return array
     * @throws \Exception
     */
    public function parse($config)
    {
        if (is_file($config)) {
            return parse_ini_file($config, true);
        } else {
            return parse_ini_string($config, true);
        }
    }
}
