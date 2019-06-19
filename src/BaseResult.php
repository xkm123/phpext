<?php
namespace php_ext;

/**
 * 基础返回类
 * Class BaseResult
 *
 * @package php_ext
 */
class BaseResult
{
    const SUCCESS = "SUCCESS";
    const FAIL = "FAIL";
    /**
     * 数据
     *
     * @var array|string|mixed
     */
    private $data = array();
    /**
     * 状态码
     *
     * @var string
     */
    private $return_code = self::SUCCESS;
    /**
     * 状态信息
     *
     * @var string
     */
    private $return_msg = 'OK';
    /**
     *  元数据
     *
     * @var mixed
     */
    private $source = '';
    /**
     *  额外的数据
     *
     * @var array
     */
    private $extend = [];

    /**
     * BaseResult constructor.
     *
     * @param array|string $data    为空则为成功
     *                              成功例子：
     *                              1.直接字符串：instance（"成功了"）
     *                              2.数组形式：一位：[成功状态信息] 两位：[状态码，状态信息] 三位：[状态码，状态信息，其他数据]
     *                              instance（['OK']）|| instance（['SUCCESS','OK']）||instance（[200,'OK']）||
     *                              instance（[200,'OK',[data1=>1,data2=>2]]）
     *                              失败例子：[状态码，状态信息，其他数据]
     *                              instance（['FAIL','ERROR']）||instance（[400,'ERROR']）||
     *                              instance（[400,'ERROR',[data1=>1,data2=>2]]）
     */
    private function __construct($data = [])
    {
        if (is_null($data)) {
            return;
        }
        if (is_string($data)) {
            $this->return_msg = $data;
        } else {
            if (count($data) == 1) {
                $this->return_msg = $data[0];
            } elseif (count($data) == 2) {
                $this->convertReturnCode($data[0]);
                $this->return_msg = $data[1];
            } elseif (count($data) == 3) {
                $this->convertReturnCode($data[0]);
                $this->return_msg = $data[1];
                $this->data = $data[2];
            }
        }
    }

    /**
     * 转换返回状态码
     *
     * @param int|string $code 状态码
     */
    private function convertReturnCode($code)
    {
        if (is_numeric($code) && $code != 200) {
            $this->return_code = self::FAIL;
        } elseif (is_numeric($code) && $code == 200) {
            $this->return_code = self::SUCCESS;
        } elseif (is_string($code)) {
            $this->return_code = strtoupper($code);
        } else {
            throw new \Exception("未知类型的code码");
        }
    }

    /**
     * 对象的初始化方法
     *
     * @param array|string $data    为空则为成功
     *                              成功例子：
     *                              1.直接字符串：instance（"成功了"）
     *                              2.数组形式：一位：[成功状态信息] 两位：[状态码，状态信息] 三位：[状态码，状态信息，其他数据]
     *                              instance（['OK']）|| instance（['SUCCESS','OK']）||instance（[200,'OK']）||
     *                              instance（[200,'OK',[data1=>1,data2=>2]]）
     *                              失败例子：[状态码，状态信息，其他数据]
     *                              instance（['FAIL','ERROR']）||instance（[400,'ERROR']）||
     *                              instance（[400,'ERROR',[data1=>1,data2=>2]]）
     *
     * @return BaseResult
     */
    public static function instance($data = [])
    {
        return new BaseResult($data);
    }

    /**
     * 设置返回的信息
     *
     * @param string $message
     *
     * @return BaseResult
     */
    public function setMessage($message)
    {
        $this->return_msg = $message;
        return $this;
    }

    /**
     *  获取返回的信息
     *
     * @return string
     *   获取返回的信息
     */
    public function getMessage()
    {
        return $this->return_msg;
    }

    /**
     * 获取数据
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 返回数据
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 获取原始数据
     *
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * 返回原始数据
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function setSource($data)
    {
        $this->source = $data;
        return $this;
    }

    /**
     * 获取扩展数据
     *
     * @return mixed
     */
    public function getExtend()
    {
        return $this->extend;
    }

    /**
     * 返回扩展数据
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function setExtend($data)
    {
        $this->extend = $data;
        return $this;
    }

    /**
     *  判断本次处理是否成功
     *
     * @param string $compare_code 比较状态码 默认：SUCCESS
     *
     * @return bool 成功true 失败false
     */
    public function isSuccess($compare_code = 'SUCCESS')
    {
        return $this->return_code == strtoupper($compare_code);
    }

    /**
     * 获取状态码
     *
     * @return string
     */
    public function getCode()
    {
        return $this->return_code;
    }

    /**
     * 设置状态码
     *
     * @param string $return_code 返回状态码
     *
     * @return $this
     */
    public function setCode($return_code)
    {
        $this->return_code = strtoupper($return_code);
        return $this;
    }

    /**
     * 将返回对象转换为数组 [return_code=>'SUCCESS',return_msg=>'OK','data'=>[]]
     *
     * @return  array
     */
    public function toArray()
    {
        return [
            'return_code' => $this->return_code,
            'return_msg' => $this->return_msg,
            'data' => $this->data
        ];
    }
}
