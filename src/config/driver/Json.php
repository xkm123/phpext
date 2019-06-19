<?php
namespace php_ext\config\driver;

use php_ext\config\interf\ConfigDriverInterface;

/**
 * 解析JSON配置文件
 * Class Json
 *
 * @package php_ext\config\driver;
 */
class Json implements ConfigDriverInterface
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
            $config = file_get_contents($config);
        }
        $result = json_decode($config, true);
        return $result;
    }
}
