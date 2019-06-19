<?php
namespace php_ext\config\driver;

use php_ext\config\interf\ConfigDriverInterface;

/**
 * 解析Yaml配置文件
 * Class Yaml
 *
 * @package php_ext\config\driver
 */
class Yaml implements ConfigDriverInterface
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
            return yaml_parse_file($config);
        } else {
            return yaml_parse($config);
        }
    }
}
