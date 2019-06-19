<?php
namespace php_ext;

/**
 * Cookie管理类
 * Class Cookies  保存,读取,更新,清除cookies数据。可设置前缀。强制超时。数据可以是字符串,数组,对象等。
 *
 * @package php_ext
 * Func:
 * public   set        设置cookie
 * public   get        读取cookie
 * public   update     更新cookie
 * public   clear      清除cookie
 * public   setPrefix  设置前缀
 * public   setExpire  设置过期时间
 * private  authCode   加密/解密
 * private  pack       将数据打包
 * private  unpack     将数据解包
 * private  getName    获取cookie name,增加prefix处理
 */
class Cookie
{
    private $prefix = '';                                                  // cookie prefix
    private $secureKey = 'ekOt4_Uu0f7XA-fJcpBvRDrg506jpcuJetxeKgPNnALr';   // encrypt key
    private $expire = 3600;                                                // default expire

    /**初始化
     *
     * @param String $prefix    cookie prefix
     * @param int    $expire    过期时间
     * @param String $secureKey cookie secure key
     */
    public function __construct($prefix = '', $expire = 0, $secureKey = '')
    {
        if (is_string($prefix) && $prefix != '') {
            $this->prefix = $prefix;
        }
        if (is_numeric($expire) && $expire > 0) {
            $this->expire = $expire;
        }
        if (is_string($secureKey) && $secureKey != '') {
            $this->secureKey = $secureKey;
        }
    }

    /**
     * 设置cookie
     *
     * @param String $name     cookie name
     * @param mixed  $value    cookie value 可以是字符串,数组,对象等
     * @param int    $expire   过期时间
     * @param string $path     路径
     * @param string $domain   主域名
     * @param bool   $secure   它表示创建的 cookie 只能在 HTTPS 连接中被浏览器传递到服务器端进行会话验证，
     *                         如果是 HTTP 连接则不会传递该信息，所以绝对不会被窃到
     * @param bool   $httpOnly 是否仅http时使用，设置了HttpOnly属性，那么通过js脚本将无法读取到cookie信息，这样能有效的防止XSS攻击
     */
    public function set($name, $value, $expire = 0, $path = "/", $domain = "", $secure = false, $httpOnly = false)
    {
        $cookie_name = $this->getName($name);
        $cookie_expire = time() + ($expire ? $expire : $this->expire);
        $cookie_value = $this->pack($value, $cookie_expire);
        $cookie_value = $this->authCode($cookie_value, 'ENCODE');
        if ($cookie_name && $cookie_value && $cookie_expire) {
            setcookie($cookie_name, $cookie_value, $cookie_expire, $path, $domain, $secure, $httpOnly);
        }
    }

    /**获取cookie name
     *
     * @param  String $name
     *
     * @return String
     */
    private function getName($name)
    {
        return $this->prefix ? $this->prefix . '_' . $name : $name;
    }

    /**pack
     *
     * @param  Mixed $data   数据
     * @param  int   $expire 过期时间 用于判断
     *
     * @return string
     */
    private function pack($data, $expire)
    {
        if ($data === '') {
            return '';
        }
        $cookie_data = array();
        $cookie_data['value'] = $data;
        $cookie_data['expire'] = $expire;
        return json_encode($cookie_data);
    }

    /**加密/解密数据
     *
     * @param  String $string    原文或密文
     * @param  String $operation ENCODE or DECODE
     *
     * @return String            根据设置返回明文活密文
     */
    private function authCode($string, $operation = 'DECODE')
    {
        $ckey_length = 4;   // 随机密钥长度 取值 0-32;
        $key = $this->secureKey;
        $key = md5($key);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) :
            substr(md5(microtime()), -$ckey_length)) : '';
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
            sprintf('%010d', 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = $this->handleData($string, $cryptkey, $key_length, $string_length);
        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
                substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)
            ) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    /**
     * 加密解密处理数据
     *
     * @param $string
     * @param $cryptkey
     * @param $key_length
     * @param $string_length
     *
     * @return string
     */
    private function handleData($string, $cryptkey, $key_length, $string_length)
    {
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        return $result;
    }

    /**读取cookie
     *
     * @param  String $name cookie name
     *
     * @return mixed          cookie value
     */
    public function get($name)
    {
        $cookie_name = $this->getName($name);
        if (isset($_COOKIE[$cookie_name])) {
            $cookie_value = $this->authCode($_COOKIE[$cookie_name], 'DECODE');
            $cookie_value = $this->unpack($cookie_value);
            return isset($cookie_value[0]) ? $cookie_value[0] : null;
        } else {
            return null;
        }
    }

    /**
     * unpack
     *
     * @param  Mixed $data 数据
     *
     * @return              array(数据,过期时间)
     */
    private function unpack($data)
    {
        if ($data === '') {
            return array('', 0);
        }
        $cookie_data = json_decode($data, true);
        if (isset($cookie_data['value']) && isset($cookie_data['expire'])) {
            if (time() < $cookie_data['expire']) { // 未过期
                return array($cookie_data['value'], $cookie_data['expire']);
            }
        }
        return array('', 0);
    }

    /**
     * 更新cookie,只更新内容,如需要更新过期时间请使用set方法
     *
     * @param String $name     cookie name
     * @param mixed  $value    cookie value 可以是字符串,数组,对象等
     * @param string $path     路径
     * @param string $domain   主域名
     * @param bool   $secure   它表示创建的 cookie 只能在 HTTPS 连接中被浏览器传递到服务器端进行会话验证，
     *                         如果是 HTTP 连接则不会传递该信息，所以绝对不会被窃到
     * @param bool   $httpOnly 是否仅http时使用，设置了HttpOnly属性，那么通过js脚本将无法读取到cookie信息，这样能有效的防止XSS攻击
     *
     * @return boolean
     */
    public function update($name, $value, $path = "/", $domain = "", $secure = false, $httpOnly = false)
    {
        $cookie_name = $this->getName($name);
        if (isset($_COOKIE[$cookie_name])) {
            $old_cookie_value = $this->authCode($_COOKIE[$cookie_name], 'DECODE');
            $old_cookie_value = $this->unpack($old_cookie_value);
            if (isset($old_cookie_value[1]) && $old_cookie_value[1] > 0) { // 获取之前的过期时间
                $cookie_expire = $old_cookie_value[1];
                // 更新数据
                $cookie_value = $this->pack($value, $cookie_expire);
                $cookie_value = $this->authCode($cookie_value, 'ENCODE');
                if ($cookie_name && $cookie_value && $cookie_expire) {
                    setcookie($cookie_name, $cookie_value, $cookie_expire, $path, $domain, $secure, $httpOnly);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 清除cookie
     *
     * @param  string $name cookie name
     * @param string  $path 路径
     */
    public function clear($name, $path = '/')
    {
        $cookie_name = $this->getName($name);
        setcookie($cookie_name, null, 0, $path);
    }

    /**设置前缀
     *
     * @param String $prefix cookie prefix
     */
    public function setPrefix($prefix)
    {
        if (is_string($prefix) && $prefix != '') {
            $this->prefix = $prefix;
        }
    }

    /**设置过期时间
     *
     * @param int $expire cookie expire
     */
    public function setExpire($expire)
    {
        if (is_numeric($expire) && $expire > 0) {
            $this->expire = $expire;
        }
    }
}
