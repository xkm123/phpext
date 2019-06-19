<?php
namespace php_ext\config\interf;

/**
 * 配置文件驱动接口
 * Interface ConfigDriverInterface
 *
 * @package php_ext\interf
 */
interface ConfigDriverInterface
{
    /**
     * 解析
     *
     * @param string $config 配置文件或内容
     *
     * @return array
     * @throws \Exception
     */
    function parse($config);
}