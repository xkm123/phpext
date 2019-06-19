<?php
namespace php_ext\parser;

class FormDataParser
{
    /**
     * 解码FormData
     *
     * @param string $content  内容
     * @param string $boundary 内容边界
     *
     * @return array
     */
    public static function decode($content, $boundary = null)
    {
        $data = [];
        try {
            if (empty($boundary)) {
                preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
                $boundary = $matches[1];
            }
            $blocks = preg_split("/-+$boundary/", $content);
            array_pop($blocks);
            foreach ($blocks as $id => $block) {
                if (empty($block)) {
                    continue;
                }
                if (strpos($block, 'application/octet-stream') !== false) {
                    preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                } else {
                    preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                }
                $data[$matches[1]] = $matches[2];
            }
        } catch (\Exception $e) {
        }
        return $data;
    }
}
