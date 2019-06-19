<?php
namespace php_ext\config\driver;

use php_ext\config\interf\ConfigDriverInterface;

/**
 * 解析XML配置文件
 * Class Xml
 *
 * @package php_ext\config\driver
 */
class Xml implements ConfigDriverInterface
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
            $content = simplexml_load_file($config);
        } else {
            $content = simplexml_load_string($config);
        }
        $result = (array)$content;
        foreach ($result as $key => $val) {
            if (is_object($val)) {
                $result[$key] = (array)$val;
            }
        }
        return $result;
    }
}
