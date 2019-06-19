<?php
namespace php_ext;

/**
 * 封装基于curl的HTTP类库
 * Class HttpTool
 *
 * @package php_ext
 * 使用：
 *  //初始化
 * $http = HttpUtils::instance();
 * //设置超时时间为60秒
 * $http->setTimeout(60);
 * //设置浏览器信息
 * $http->setUserAgent($_SERVER['HTTP_USER_AGENT']);
 * //设置请求的方式为XMLHttpRequest，模拟ajax请求信息，可用于某些api的限制，此处仅为演示说明
 * $http->setHeader('X-Requested-With', 'XMLHttpRequest');
 * //设置代理服务器信息
 * $http->setProxy('59.108.116.175', '3128');
 * //请求资源
 * $http->request('http://www.baidu.com/s?wd=ip');
 * //获取网页数据
 * $data = $http->getData();
 * //获取cookie（默认返回字符串，参数为true时返回数组）
 * $cookie = $http->getCookie();
 * //获取网页响应状态码
 * $stateCode = $http->getStateCode();
 * //获取原始未处理的响应信息
 * $response = $http->getResponse();
 * //获取连接资源的信息（返回数组），如平均上传速度 、平均下载速度 、请求的URL等
 * $response_info = $http->getInfo();
 */
class HttpUtils
{
    /**
     * 响应结果
     *
     * @var mixed
     */
    private $response;
    /**
     * 响应头
     *
     * @var array
     */
    private $responseHeader = [];
    /**
     * 响应数据
     *
     * @var string
     */
    private $responseData = "";
    /**
     * 响应信息
     *
     * @var mixed
     */
    private $responseInfo;
    /**
     * 响应状态码
     *
     * @var int
     */
    private $responseHttpCode;
    /**
     * 超时时间
     *
     * @var int
     */
    private $timeout = 30;
    /**
     * 缓存
     *
     * @var string
     */
    private $cookie = '';
    /**
     * 用户协议
     *
     * @var string
     */
    private $userAgent = 'PHP-HttpLib-v1.0';
    /**
     * 请求头
     *
     * @var array
     */
    private $requestHeader = array();
    /**
     * 请求时间
     *
     * @var float|int
     */
    private $startTime;
    /**
     * 请求代理
     *
     * @var array
     */
    private $requestProxy = array();
    /**
     * 请求方式
     *
     * @var string
     */
    private $requestMethod = 'POST';
    /**
     * 端口
     *
     * @var int
     */
    private $port = 0;
    /**
     * ssl配置
     *
     * @var array
     */
    private $ssl = ['peer' => false, 'host' => false, 'type' => 'PEM', 'cert' => '', 'password' => '',
        'key_type' => 'PEM', 'key' => ''];
    /**
     * 请求方式集合
     *
     * @var array
     */
    private $methods = ['GET' => 0, 'POST' => 1, 'PUT' => 2, 'DELETE' => 3, 'PATCH' => 4];
    /**
     * 基础路径
     *
     * @var string
     */
    private $baseUri = "";
    /**
     * 协议数组
     *
     * @var array
     */
    private $agentArray = [
        //PC端的UserAgent
        "safari 5.1 – MAC" => "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11",
        "safari 5.1 – Windows" => "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50",
        "Firefox 38esr" => "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0",
        "IE 11" => "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; .NET4.0C; .NET4.0E; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; InfoPath.3; rv:11.0) like Gecko",
        "IE 9.0" => "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0",
        "IE 8.0" => "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)",
        "IE 7.0" => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)",
        "IE 6.0" => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)",
        "Firefox 4.0.1 – MAC" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
        "Firefox 4.0.1 – Windows" => "Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
        "Opera 11.11 – MAC" => "Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; en) Presto/2.8.131 Version/11.11",
        "Opera 11.11 – Windows" => "Opera/9.80 (Windows NT 6.1; U; en) Presto/2.8.131 Version/11.11",
        "Chrome 17.0 – MAC" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_0) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11",
        "傲游（Maxthon）" => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Maxthon 2.0)",
        "腾讯TT" => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; TencentTraveler 4.0)",
        "世界之窗（The World） 2.x" => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
        "世界之窗（The World） 3.x" => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; The World)",
        "360浏览器" => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; 360SE)",
        "搜狗浏览器 1.x" => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; SE 2.X MetaSr 1.0; SE 2.X MetaSr 1.0; .NET CLR 2.0.50727; SE 2.X MetaSr 1.0)",
        "Avant" => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Avant Browser)",
        "Green Browser" => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
        //移动端口
        "safari iOS 4.33 – iPhone" => "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",
        "safari iOS 4.33 – iPod Touch" => "Mozilla/5.0 (iPod; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",
        "safari iOS 4.33 – iPad" => "Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",
        "Android N1" => "Mozilla/5.0 (Linux; U; Android 2.3.7; en-us; Nexus One Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
        "Android QQ浏览器 For android" => "MQQBrowser/26 Mozilla/5.0 (Linux; U; Android 2.3.7; zh-cn; MB200 Build/GRJ22; CyanogenMod-7) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
        "Android Opera Mobile" => "Opera/9.80 (Android 2.3.4; Linux; Opera Mobi/build-1107180945; U; en-GB) Presto/2.8.149 Version/11.10",
        "Android Pad Moto Xoom" => "Mozilla/5.0 (Linux; U; Android 3.0; en-us; Xoom Build/HRI39) AppleWebKit/534.13 (KHTML, like Gecko) Version/4.0 Safari/534.13",
        "BlackBerry" => "Mozilla/5.0 (BlackBerry; U; BlackBerry 9800; en) AppleWebKit/534.1+ (KHTML, like Gecko) Version/6.0.0.337 Mobile Safari/534.1+",
        "WebOS HP Touchpad" => "Mozilla/5.0 (hp-tablet; Linux; hpwOS/3.0.0; U; en-US) AppleWebKit/534.6 (KHTML, like Gecko) wOSBrowser/233.70 Safari/534.6 TouchPad/1.0",
        "UC标准" => "NOKIA5700/ UCWEB7.0.2.37/28/999",
        "UCOpenwave" => "Openwave/ UCWEB7.0.2.37/28/999",
        "UC Opera" => "Mozilla/4.0 (compatible; MSIE 6.0; ) Opera/UCWEB7.0.2.37/28/999",
        "微信内置浏览器" => "Mozilla/5.0 (Linux; Android 6.0; 1503-M02 Build/MRA58K) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile MQQBrowser/6.2 TBS/036558 Safari/537.36 MicroMessenger/6.3.25.861 NetType/WIFI Language/zh_CN",
    ];

    /**
     * 实例化
     *
     * @return HttpUtils
     */
    public static function instance()
    {
        return new self();
    }

    public function __construct()
    {
        $this->startTime = self::myMicrotime();
        //能有效提高POST大于1M数据时的请求速度
        $this->setHeader('Expect');
    }

    /**
     * 获取基础路径
     *
     * @return int
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * 设置基础地址
     *
     * @param string $baseUri 基础地址
     *
     * @return $this
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = empty($baseUri) ? '' : $baseUri;
        return $this;
    }

    /**
     * 获取端口
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * 设置端口
     *
     * @param int $port 端口号
     *
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * 获取ssl配置
     *
     * @return array
     */
    public function getSsl()
    {
        return $this->ssl;
    }

    /**
     * 设置ssl配置
     *
     * @param array $ssl
     *
     * @return  $this
     */
    public function setSsl($ssl)
    {
        $this->ssl = array_merge($this->ssl, $ssl);
        return $this;
    }

    /**
     * 设置cookie(可选)
     *
     * @param string|array $cookie 缓存
     *
     * @return  $this
     */
    public function setCookie($cookie = '')
    {
        if (is_array($cookie)) {
            $cookies = array();
            foreach ($cookie as $k => $v) {
                $cookies[] = $k . '=' . $v;
            }
            $this->cookie .= implode('; ', $cookies);
        } else {
            $this->cookie .= $cookie;
        }
        return $this;
    }

    /**
     * 设置用户浏览器信息(可选)
     *
     * @param string $userAgent 浏览器代理信息
     *
     * @return  $this
     */
    public function setUserAgent($userAgent = '')
    {
        if (empty($userAgent)) {
            //随机浏览器userAgent
            $this->userAgent = $this->agentArray[array_rand($this->agentArray, 1)];
        } else {
            $this->userAgent = $userAgent;
        }
        return $this;
    }

    /**
     * 设置头部伪造IP(可选)，对某些依靠头部信息判断ip的网站有效
     *
     * @param string $ip ： 需要伪造的ip
     *
     * @return  $this
     */
    public function setForgeIp($ip)
    {
        if (empty($ip)) {
            $ip = rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);
        }
        $this->setHeader('CLIENT-IP', $ip);
        $this->setHeader('X-FORWARDED-FOR', $ip);
        $this->setHeader('Remote Address', $ip);
        return $this;
    }

    /**
     * 添加请求头部信息
     *
     * @param string $k 键
     * @param string $v 值
     *
     * @return  $this
     */
    public function setHeader($k, $v = '')
    {
        if (!empty($k)) {
            $this->requestHeader[] = $k . ':' . $v;
        }
        return $this;
    }

    /**
     * 设置方法请求类型
     *
     * @param string $method 方法类型
     *
     * @throws
     * @return $this
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);
        if (!isset($this->methods[$method])) {
            throw new \Exception('无效的请求方式：' . $method);
        }
        $this->requestMethod = $method;
        return $this;
    }

    /**
     * 设置超时时间(可选)
     *
     * @param int $sec 超时时间
     *
     * @return  $this
     */
    public function setTimeout($sec)
    {
        if ($sec > ini_get('max_execution_time')) {
            @set_time_limit($sec);
        }
        $this->timeout = $sec;
        return $this;
    }

    /**
     * 设置代理(可选)，如对方限制了ip访问或需要隐藏真实ip
     *
     * @param string     $host 代理服务器（ip或者域名）
     * @param int|string $port 代理服务端口
     * @param string     $user 用户名（如果有）
     * @param string     $pass 用户密码（如果有）
     *
     * @return  $this
     */
    public function setProxy($host, $port = '', $user = '', $pass = '')
    {
        $this->requestProxy = array('host' => $host, 'port' => $port, 'user' => $user, 'pass' => $pass);
        return $this;
    }

    /**
     * 请求url
     *
     * @param string       $url      请求的地址
     * @param array|string $postData 该项如果填写则为POST方式，否则为GET方式;如需上传文件,需在文件路径前加上@符号
     * @param string       $referer  来路地址，用于伪造来路
     *
     * @return $this
     */
    public function request($url, $postData = '', $referer = '')
    {
        if (!(self::startsWith($url, "http://") || self::startsWith($url, "https://"))) {
            $url = $this->baseUri . $url;
        }
        $ch = $this->httpRequest($url, $postData, $referer);
        $this->response = curl_exec($ch);
        if ($this->response === false) {
            $this->responseHttpCode = 204; //没有内容，请求失败
            $this->responseData = 'URL：' . $url . ' CURL resource: ' . (string)$ch . '; CURL error: ' .
                curl_error($ch) . ' (' . curl_errno($ch) . ')';
            return $this;
        }
        $this->processResponse($ch);
        curl_close($ch);
        return $this;
    }

    /**
     * 检查字符串是否以某些字符串开头
     *
     * @param  string       $haystack
     * @param  string|array $needles
     *
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && mb_strpos($haystack, $needle) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取响应的所有信息
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * 获取响应的header信息
     *
     * @param string $key : (可选)header头部信息标示如获取Set-Cookie
     *
     * @return array|null|string
     */
    public function getHeader($key = '')
    {
        if ($key == '') {
            return $this->responseHeader;
        }
        if (!isset($this->responseHeader[$key])) {
            return null;
        }
        return $this->responseHeader[$key];
    }

    /**
     * 获取响应的cookie
     *
     * @param boolean $assoc : 选择true将返回数组否则返回字符串
     *
     * @return array|null|string
     */
    public function getCookie($assoc = false)
    {
        $cookies = $this->getHeader('Set-Cookie');
        if ($cookies) {
            foreach ($cookies as $v) {
                $cookieInfo = explode(';', $v);
                if (strpos($this->cookie, $cookieInfo[0]) === false) {
                    $this->cookie .= $cookieInfo[0];
                    $this->cookie .= '; ';
                }
            }
        }
        if ($assoc && $this->cookie) {
            $cookie = substr($this->cookie, 0, -3);
            $cookie = explode('; ', $cookie);
            $cookies = array();
            foreach ($cookie as $v) {
                $cookieInfo = explode('=', $v);
                $cookies[$cookieInfo[0]] = $cookieInfo[1];
            }
            return $cookies;
        }
        return $this->cookie;
    }

    /**
     * 获取响应的页面数据即页面代码
     *
     * @return string
     */
    public function getData()
    {
        return $this->responseData;
    }

    /**
     * 获取响应的网页编码
     *
     * @return null|string
     */
    public function getCharset()
    {
        $contentType = $this->getHeader('Content-Type');
        if ($contentType) {
            preg_match('/charset=(.+)/i', $contentType, $matches);
            if (isset($matches[1])) {
                return strtoupper(trim($matches[1]));
            }
        }
        return null;
    }

    /**
     * 获取连接资源的信息（返回数组）
     *
     * @return mixed
     */
    public function getInfo()
    {
        return $this->responseInfo;
    }

    /**
     * 获取响应的网页状态码 (注：200为正常响应)
     *
     * @return int
     */
    public function getStateCode()
    {
        return $this->responseHttpCode;
    }

    /**
     * 获取请求方法
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->requestMethod;
    }

    /**
     * 获取请求时间
     *
     * @return float|int
     */
    public function getRequestTime()
    {
        return self::myMicrotime() - $this->startTime;
    }

    /**
     * 处理响应的数据
     *
     * @param resource $ch http指针
     *
     * @return bool
     */
    private function processResponse($ch = null)
    {
        if (is_resource($ch)) {
            $content_size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
            if ($content_size > 0) {
                $this->responseHeader = substr($this->response, 0, -$content_size);
                $this->responseData = substr($this->response, -$content_size);
            } else {
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $this->responseHeader = substr($this->response, 0, $header_size);
                $this->responseData = substr($this->response, $header_size);
            }
            $this->responseHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $this->responseInfo = curl_getinfo($ch);
            //分解响应头部信息
            $this->responseHeader = explode("\r\n\r\n", trim($this->responseHeader));
            $this->responseHeader = array_pop($this->responseHeader);
            $this->responseHeader = explode("\r\n", $this->responseHeader);
            array_shift($this->responseHeader); //开头为状态
            //分割数组
            $header_assoc = array();
            foreach ($this->responseHeader as $header) {
                $kv = explode(': ', $header);
                if ($kv[0] == 'Set-Cookie') {
                    $header_assoc['Set-Cookie'][] = $kv[1];
                } else {
                    $header_assoc[$kv[0]] = $kv[1];
                }
            }
            $this->responseHeader = $header_assoc;
        }
        return false;
    }

    /**
     * 请求数据
     *
     * @param string       $url       请求地址
     * @param string|array $post_data 请求参数
     * @param string       $referer   当你用libcurlAPI来请求某些数据时，发现返回的数据是0，
     *                                这时候你就要去尝试用CURLOPT_REFERER来伪造一个来路页面
     *
     * @return resource
     */
    private function httpRequest($url, $post_data, $referer = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($this->port > 0) {
            curl_setopt($ch, CURLOPT_PORT, $this->port);//指定端口
        }
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//是否要求返回数据
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->ssl['peer']);//是否检测服务器的证书是否由正规浏览器认证过的授权CA颁发的
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->ssl['host']);//是否检测服务器的域名与证书上的是否一致
        if (!empty($this->ssl['cert'])) {
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, $this->ssl['type']);//证书类型，"PEM" (default), "DER", and"ENG".
            curl_setopt($ch, CURLOPT_SSLCERT, $this->ssl['cert']);//证书存放路径
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->ssl['password']);//证书密码,没有可以留空
        }
        if (!empty($this->ssl['key'])) {
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, $this->ssl['key_type']);//私钥类型，"PEM" (default), "DER", and"ENG".
            curl_setopt($ch, CURLOPT_SSLKEY, $this->ssl['key']);//私钥存放路径
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        //强制使用IPV4协议解析域名，否则在支持IPV6的环境下请求会异常慢
        @curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        if ($post_data) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->requestMethod); //设置请求方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        if ($this->cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        }
        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        if ($this->requestHeader) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->requestHeader);
        }
        if ($this->requestProxy) {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
            $host = $this->requestProxy['host'];
            $host .= ($this->requestProxy['port']) ? ':' . $this->requestProxy['port'] : '';
            curl_setopt($ch, CURLOPT_PROXY, $host);
            if (isset($this->requestProxy['user']) && isset($this->requestProxy['pass'])) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->requestProxy['user'] . ':' . $this->requestProxy['pass']);
            }
        }
        return $ch;
    }

    /**
     * 获取毫秒数
     *
     * @return float|int
     */
    private static function myMicrotime()
    {
        return array_sum(explode(' ', microtime()));
    }

}
