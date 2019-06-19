<?php
namespace php_ext\config\driver;

use php_ext\config\interf\ConfigDriverInterface;
use php_ext\FileUtils;

/**
 * 解析Php配置文件
 * Class Php
 *
 * @package php_ext\config\driver
 */
class Php implements ConfigDriverInterface
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
        $isCreateFile = false;
        $result = [];
        if (!file_exists($config) && is_string($config)) {
            $content = $config;
            $cachePath = __DIR__ . '/cache_parser/';
            FileUtils::createDir($cachePath);
            $config = $cachePath . md5($config . time()) . ".php";
            $writeRes = file_put_contents($config, $content);
            if (!$writeRes) {
                return $result;
            }
            $isCreateFile = true;
        }
        $result = $this->parseFile($config);
        if ($isCreateFile && file_exists($config)) {
            unlink($config);
        }
        return $result;
    }

    /**
     * 解析文件
     *
     * @param string $path 路径
     *
     * @return array|mixed
     */
    private function parseFile($path)
    {
        if (is_file($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            if ('php' == $type) {
                $arr = require($path);
                return $arr;
            }
        }
        return [];
    }
}
