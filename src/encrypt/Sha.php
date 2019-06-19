<?php
namespace php_ext\encrypt;

class Sha
{
    /**
     * 以下代码实现PHP sha256() sha256_file() sha512() sha512_file() PHP 5.1.2+完美兼容
     *
     * @param string  $data      要计算散列值的字符串
     * @param boolean $rawOutput 为true时返回原始二进制数据，否则返回字符串
     *
     * @return boolean | string 参数无效或者文件不存在或者文件不可读时返回false，计算成功则返回对应的散列值
     * @notes 使用示例 sha256('mrdede.com') sha512('mrdede.com') sha256_file('index.php') sha512_file('index.php')
     */
    public static function sha1($data, $rawOutput = false)
    {
        if (!is_scalar($data)) {
            return false;
        }
        $data = (string)$data;
        $rawOutput = !!$rawOutput;
        return sha1($data, $rawOutput);
    }

    /**
     * 以下代码实现PHP sha256() sha256_file() sha512() sha512_file() PHP 5.1.2+完美兼容
     *
     * @param boolean $rawOutput 为true时返回原始二进制数据，否则返回字符串
     * @param string  $file      要计算散列值的文件名，可以是单独的文件名，也可以包含路径，绝对路径相对路径都可以
     *
     * @return boolean | string 参数无效或者文件不存在或者文件不可读时返回false，计算成功则返回对应的散列值
     * @notes 使用示例 sha256('mrdede.com') sha512('mrdede.com') sha256_file('index.php') sha512_file('index.php')
     */
    public static function sha1File($file, $rawOutput = false)
    {
        if (!is_scalar($file)) {
            return false;
        }
        $file = (string)$file;
        if (!is_file($file) || !is_readable($file)) {
            return false;
        }
        $rawOutput = !!$rawOutput;
        return sha1_file($file, $rawOutput);
    }

    /**
     * 以下代码实现PHP sha256() sha256_file() sha512() sha512_file() PHP 5.1.2+完美兼容
     *
     * @param string  $data      要计算散列值的字符串
     * @param boolean $rawOutput 为true时返回原始二进制数据，否则返回字符串
     *
     * @return boolean | string 参数无效或者文件不存在或者文件不可读时返回false，计算成功则返回对应的散列值
     * @notes 使用示例 sha256('mrdede.com') sha512('mrdede.com') sha256_file('index.php') sha512_file('index.php')
     */
    public static function sha256($data, $rawOutput = false)
    {
        if (!is_scalar($data)) {
            return false;
        }
        $data = (string)$data;
        $rawOutput = !!$rawOutput;
        return hash('sha256', $data, $rawOutput);
    }

    /**
     * 以下代码实现PHP sha256() sha256_file() sha512() sha512_file() PHP 5.1.2+完美兼容
     *
     * @param boolean $rawOutput 为true时返回原始二进制数据，否则返回字符串
     * @param string  $file      要计算散列值的文件名，可以是单独的文件名，也可以包含路径，绝对路径相对路径都可以
     *
     * @return boolean | string 参数无效或者文件不存在或者文件不可读时返回false，计算成功则返回对应的散列值
     * @notes 使用示例 sha256('mrdede.com') sha512('mrdede.com') sha256_file('index.php') sha512_file('index.php')
     */
    public static function sha256File($file, $rawOutput = false)
    {
        if (!is_scalar($file)) {
            return false;
        }
        $file = (string)$file;
        if (!is_file($file) || !is_readable($file)) {
            return false;
        }
        $rawOutput = !!$rawOutput;
        return hash_file('sha256', $file, $rawOutput);
    }

    /**
     * 以下代码实现PHP sha256() sha256_file() sha512() sha512_file() PHP 5.1.2+完美兼容
     *
     * @param string  $data      要计算散列值的字符串
     * @param boolean $rawOutput 为true时返回原始二进制数据，否则返回字符串
     *
     * @return boolean | string 参数无效或者文件不存在或者文件不可读时返回false，计算成功则返回对应的散列值
     * @notes 使用示例 sha256('mrdede.com') sha512('mrdede.com') sha256_file('index.php') sha512_file('index.php')
     */
    public static function sha512($data, $rawOutput = false)
    {
        if (!is_scalar($data)) {
            return false;
        }
        $data = (string)$data;
        $rawOutput = !!$rawOutput;
        return hash('sha512', $data, $rawOutput);
    }

    /**
     * 以下代码实现PHP sha256() sha256_file() sha512() sha512_file() PHP 5.1.2+完美兼容
     *
     * @param boolean $rawOutput 为true时返回原始二进制数据，否则返回字符串
     * @param string  $file      要计算散列值的文件名，可以是单独的文件名，也可以包含路径，绝对路径相对路径都可以
     *
     * @return boolean | string 参数无效或者文件不存在或者文件不可读时返回false，计算成功则返回对应的散列值
     * @notes 使用示例 sha256('mrdede.com') sha512('mrdede.com') sha256_file('index.php') sha512_file('index.php')
     */
    public static function sha512File($file, $rawOutput = false)
    {
        if (!is_scalar($file)) {
            return false;
        }
        $file = (string)$file;
        if (!is_file($file) || !is_readable($file)) {
            return false;
        }
        $rawOutput = !!$rawOutput;
        return hash_file('sha512', $file, $rawOutput);
    }
}
