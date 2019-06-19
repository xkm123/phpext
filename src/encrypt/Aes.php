<?php
namespace php_ext\encrypt;

/**
 * Aes-128-cbc 加密库
 * Class Aes
 *
 * @package php_ext\encrypt
 */
class Aes
{
    /**
     * [encrypt aes加密]
     *
     * @param  string $input [要加密的数据]
     * @param  string $key   [加密key]
     * @param  string $iv    [向量]
     *
     * @return string       [加密后的数据]
     */
    public static function encrypt($input, $key, $iv = "")
    {
        if (version_compare(PHP_VERSION, '7.1.0', 'ge')) {
            $iv = self::getIv($key, $iv);
            $data = openssl_encrypt($input, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
            $data = base64_encode($data);
            return $data;
        }
        return self::encryptOld($input, $key, $iv);
    }

    /**
     * [decrypt aes解密]
     *
     * @param  string $input [要解密的数据]
     * @param  string $key   [加密key]
     * @param  string $iv    [向量]
     *
     * @return string      [解密后的数据]
     */
    public static function decrypt($input, $key, $iv = "")
    {
        if (version_compare(PHP_VERSION, '7.1.0', 'ge')) {
            $iv = self::getIv($key, $iv);
            $decrypted = openssl_decrypt(base64_decode($input), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
            return $decrypted;
        }
        return self::decryptOld($input, $key, $iv);
    }

    /**
     * [encrypt aes加密]
     *
     * @param  string $input [要加密的数据]
     * @param  string $key   [加密key]
     * @param  string $iv    偏移量
     *
     * @return string       [加密后的数据]
     */
    public static function encryptOld($input, $key, $iv)
    {
        $iv = self::getIv($key, $iv);
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $iv);
        mcrypt_generic_init($module, $key, $iv);
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $pad = $block - (strlen($input) % $block);
        $input .= str_repeat(chr($pad), $pad);
        $encrypted = mcrypt_generic($module, $input);
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        return base64_encode($encrypted);
    }

    /**
     * [decrypt aes解密]
     *
     * @param  string $input [要解密的数据]
     * @param  string $key   [加密key]
     * @param  string $iv    偏移量
     *
     * @return string      [解密后的数据]
     */
    public static function decryptOld($input, $key, $iv = "")
    {
        $iv = self::getIv($key, $iv);
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $iv);
        mcrypt_generic_init($module, $key, $iv);
        $encryptedData = base64_decode($input);
        $encryptedData = mdecrypt_generic($module, $encryptedData);
        $e = ord($encryptedData[strlen($encryptedData) - 1]);
        if ($e <= 16) {
            $encryptedData = substr($encryptedData, 0, strlen($encryptedData) - $e);
        }
        return $encryptedData;
    }

    /**
     * 获取向量
     *
     * @param string $key 密匙
     * @param string $iv  向量
     *
     * @return string
     */
    public static function getIv($key, $iv = "")
    {
        if (empty($iv)) {
            $iv = $key;
        } else {
            $iv = substr(($iv . "0000000000000000"), 0, 16);
        }
        return $iv;
    }
}
