<?php
namespace php_ext\parser;

use ReflectionClass;
use stdClass;

/**
 * 对象解析器
 * Class ObjectParser
 *
 * @package php_ext\parser
 */
class ObjectParser
{
    /**
     * 将数组转换为对象
     *
     * @param array $data 数据
     *
     * @return stdClass
     */
    public static function encode($data)
    {
        if (is_string($data)) {
            $data = self::parseParam($data);
        }
        if (is_array($data)) {
            $obj = new StdClass();
            foreach ($data as $key => $val) {
                $obj->$key = $val;
            }
        } else {
            $obj = $data;
        }
        return $obj;
    }

    /**
     * 解析数据
     *
     * @param string|object $data 数据
     *
     * @return array|null
     * @throws \ReflectionException
     */
    public static function decode($data)
    {
        if (is_null($data)) {
            return null;
        }
        if (is_array($data)) {
            return $data;
        }
        if (is_string($data)) {
            return self::parseParam($data);
        }
        if (is_object($data)) {
            return self::parseObjectToArray($data);
        } else {
            return null;
        }
    }

    /**
     * 解析内容
     *
     * @param string $content 请求内容文本
     *
     * @return array|null
     */
    private static function parseParam($content)
    {
        if (empty($content)) {
            return null;
        } else {
            $result = XmlParser::decode($content);
            if (!$result) {
                $result = QueryParser::decode($content);
                if (!$result) {
                    try {
                        $result = XmlParser::decode($content);
                    } catch (\Exception $e1) {
                        return null;
                    }
                }
            }
            return $result;
        }
    }

    /**
     * 解析对象到数组
     *
     * @param \stdClass $object
     * var_dump($reflectionClass->getProperty('staticProperty')->getValue()); //静态属性可以不加参数
     * var_dump($reflectionClass->getProperty('property')->getValue(new Foo)); //非静态属性必须加传一个类实例
     * $reflectionProperty = $reflectionClass->getProperty('privateProperty'); //受保护的属性就要通过setAccessible获得其权限
     * $reflectionProperty->setAccessible(true);
     * var_dump($reflectionProperty->getValue(new Foo));
     *
     * @return array
     * @throws \ReflectionException
     */
    private static function parseObjectToArray($object)
    {
        $result = [];
        $className = get_class($object);
        $class = new ReflectionClass($className);
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            if ($property->isPublic()) {
                if ($property->isStatic()) {
                    $temp_res = $property->getValue();
                } else {
                    $temp_res = $property->getValue($object);
                }
            } else {
                $property->setAccessible(true);
                $temp_res = $property->getValue($object);
            }
            $result[$property->getName()] = $temp_res;
        }
        $class = null;
        return $result;
    }
}
