<?php
namespace php_ext\eventbus\template;

/**
 * 事件数据包
 * Class EventData
 * User: 623279281@qq.com
 *
 * @package php_ext\eventbus\template
 */
class EventData
{
    const SUCCESS = "SUCCESS";
    const FAIL = "FAIL";
    /**
     * 事件优先级
     *
     * @var int
     */
    private $priority = 100;
    /**
     * 事件名称
     *
     * @var null | string
     */
    private $name = '';
    /**
     * 事件描述
     *
     * @var null | string
     */
    private $describe = '';
    /**
     * 是否静态函数 默认false
     *
     * @var bool
     */
    private $isStaticFunc = false;
    /**
     * 事件类名
     *
     * @var null | string
     */
    private $class = '';
    /**
     * 事件函数名
     *
     * @var null | string
     */
    private $action = 'run';
    /**
     * 原数据
     *
     * @var array
     */
    private $sourceData = [];
    /**
     * 基础数据
     *
     * @var array
     */
    private $baseData = [];
    /**
     * 结果数据
     *
     * @var array
     */
    private $resultData = [];
    /**
     * 事件集合
     *
     * @var array
     */
    private $events = [];
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
     * 异常信息
     *
     * @var null|\Exception
     */
    private $exception = null;

    /**
     * 获取异常
     *
     * @return \Exception|null
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * 设置异常
     *
     * @param \Exception|null $exception
     *
     * @return  $this
     */
    public function setException($exception)
    {
        $this->exception = $exception;
        return $this;
    }

    /**
     * 设置返回的信息
     *
     * @param string $message
     *
     * @return $this
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
     * EventData constructor.
     *
     * @param array $data 数据包
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $name = $this->convertUnderline($key);
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * 下划线转驼峰
     *
     * @param string $str 需转换的字符串
     *
     * @return string
     */
    public function convertUnderline($str)
    {
        $str = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $str);
        return $str;
    }

    /**
     * 获取事件集合
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * 添加一个事件
     *
     * @param EventData $event 事件
     *
     * @return $this
     */
    public function addEvent($event)
    {
        array_push($this->events, $event);
        return $this;
    }

    /**
     * 获取优先级
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * 设置优先级
     *
     * @param int $priority
     *
     * @return  $this
     */
    public function setPriority($priority = 100)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * 获取事件名
     *
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 设置事件名
     *
     * @param null|string $name
     *
     * @return  $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 获取事件描述
     *
     * @return null|string
     */
    public function getDescribe()
    {
        return $this->describe;
    }

    /**
     * 设置事件描述
     *
     * @param null|string $describe
     *
     * @return  $this
     */
    public function setDescribe($describe)
    {
        $this->describe = $describe;
        return $this;
    }

    /**
     * 是否是静态函数
     *
     * @return bool
     */
    public function isStaticFunc()
    {
        return $this->isStaticFunc;
    }

    /**
     * 设置是否是静态函数
     *
     * @param bool $isStaticFunc
     *
     * @return  $this
     */
    public function setIsStaticFunc($isStaticFunc)
    {
        $this->isStaticFunc = $isStaticFunc;
        return $this;
    }

    /**
     * 获取事件类
     *
     * @return null|string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * 设置事件类
     *
     * @param null|string $class
     *
     * @return  $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * 获取函数方法
     *
     * @return null|string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * 设置函数方法
     *
     * @param null|string $action
     *
     * @return  $this
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * 获取原数据信息
     *
     * @return mixed
     */
    public function getSourceData()
    {
        return $this->sourceData;
    }

    /**
     * 设置原数据信息
     *
     * @param mixed $sourceData
     *
     * @return  $this
     */
    public function setSourceData($sourceData)
    {
        $this->sourceData = $sourceData;
        return $this;
    }

    /**
     * 获取基础数据信息
     *
     * @return mixed
     */
    public function getBaseData()
    {
        return $this->baseData;
    }

    /**
     * 设置原数据信息
     *
     * @param mixed $baseData
     *
     * @return  $this
     */
    public function setBaseData($baseData)
    {
        $this->baseData = $baseData;
        return $this;
    }

    /**
     * 获取结果信息
     *
     * @return mixed
     */
    public function getResultData()
    {
        return $this->resultData;
    }

    /**
     * 设置结果信息
     *
     * @param mixed $resultData
     *
     * @return  $this
     */
    public function setResultData($resultData)
    {
        $this->resultData = $resultData;
        return $this;
    }

    /**
     * 转换数组
     *
     * @param bool $all 是否全部
     *
     * @return array
     */
    public function toArray($all = false)
    {
        $result = ['name' => $this->name, 'priority' => $this->priority, 'describe' => $this->describe,
            'class' => $this->class, 'action' => $this->action, 'isStaticFunc' => $this->isStaticFunc];
        if ($all) {
            $result = array_merge($result, ['baseData' => $this->baseData, 'sourceData' => $this->sourceData,
                'resultData' => $this->resultData, 'events' => $this->events, 'return_code' => $this->return_code,
                'return_msg' => $this->return_msg]);
        }
        return $result;
    }
}
