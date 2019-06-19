<?php
namespace php_ext\parser;

use SimpleXMLElement;

/**
 * Xml解析类
 * Class Xml
 *
 * @package php_ext\parser
 */
class XmlParser
{
    /**
     * XML to array.
     *
     * @param string $xml XML string
     *
     * @return array|\SimpleXMLElement
     */
    public static function decode($xml)
    {
        try {
            libxml_disable_entity_loader(true);
            return self::normalize(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS));
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Object to array.
     *
     * @param SimpleXMLElement $obj
     *
     * @return array
     */
    protected static function normalize($obj)
    {
        $result = null;
        if (is_object($obj)) {
            $obj = (array)$obj;
        }
        if (is_array($obj)) {
            foreach ($obj as $key => $value) {
                $res = self::normalize($value);
                if (('@attributes' === $key) && ($key)) {
                    $result = $res;
                } else {
                    $result[$key] = $res;
                }
            }
        } else {
            $result = $obj;
        }
        return $result;
    }

    /**
     * XML encode.
     *
     * @param array             $data 需要转xml的数组
     * @param string            $root 根节点名称
     * @param string            $item 如果是数组的话，数组节点名称
     * @param array|string|null $attr 属性值
     * @param string            $id   数组节点属性id
     *
     * @return string
     */
    public static function encode($data, $root = 'xml', $item = 'item', $attr = '', $id = 'id')
    {
        if (is_array($attr)) {
            $_attr = [];
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr = trim($attr);
        $attr = empty($attr) ? '' : " {$attr}";
        $xml = "<{$root}{$attr}>";
        $xml .= self::dataToXml($data, $item, $id);
        $xml .= "</{$root}>";
        return $xml;
    }

    /**
     * Array to XML.
     *
     * @param array  $data 数据
     * @param string $item item名称
     * @param string $id   id  数组节点属性id
     *
     * @return string
     */
    protected static function dataToXml($data, $item = 'item', $id = 'id')
    {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $id && $attr = " {$id}=\"{$key}\"";
                $key = $item;
            }
            $xml .= "<{$key}{$attr}>";
            if ((is_array($val) || is_object($val))) {
                $xml .= self::dataToXml((array)$val, $item, $id);
            } else {
                $xml .= is_numeric($val) ? $val : self::cdata($val);
            }
            $xml .= "</{$key}>";
        }
        return $xml;
    }

    /**
     * 构建 CDATA.
     *
     * @param string $string 字符串
     *
     * @return string
     */
    public static function cdata($string)
    {
        return sprintf('<![CDATA[%s]]>', $string);
    }
}
