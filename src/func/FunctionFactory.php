<?php
namespace php_ext\func;

use Exception;
use php_ext\Arr;
use php_ext\BaseResult;

/**
 * Class FunctionFactory
 *
 * @package php_ext\func
 */
class FunctionFactory
{
    /**
     * 条件
     *  'trans':数据库事务是否开启,
     *  'debug_type':日志调试 '  0:不记录日志 1：开启记录日志 2：记录日志并调试程序
     *   log' => ['模型', '标识']:日志写入标识
     *  'driver':驱动
     *
     * @var array 条件
     */
    private $conditions = ['trans' => false, 'debug' => 0, 'log' => ['模型', '标识'], 'driver' => ''];
    /**
     * 处理回调
     *
     * @var callable(FunctionFactory) 处理回调
     */
    private $handleCallback;
    /**
     * 结果回调
     *
     * @var callable(BaseResult) 结果回调
     */
    private $resultCallback;
    /**
     * 结果
     *
     * @var BaseResult
     */
    private $endResult;
    /**
     * 数据库驱动
     *
     * @var FunctionDriverInterface
     */
    private $driver = null;
    /**
     * 事务状态
     *
     * @var array
     */
    private $trans = [];

    /**
     * 构造方法
     * FunctionFactory constructor.
     *
     * @param callable(FunctionFactory) $handleCallback 处理回调
     * @param callable(BaseResult)      $resultCallback 结果回调
     * @param array $condition
     *
     * @throws Exception
     */
    public function __construct(callable $handleCallback, $resultCallback = null, array $condition = [])
    {
        if (count($condition) > 0) {
            $this->conditions = Arr::merge($this->conditions, $condition);
        }
        if (is_null($handleCallback)) {
            throw new Exception("handleCallback不能为null");
        }
        $class = $this->conditions['driver'];
        $this->driver = new $class();
        if (!$this->driver instanceof FunctionDriverInterface) {
            throw new Exception(get_class($this->driver) . "未继承DatabaseInterface接口");
        }
        $this->handleCallback = $handleCallback;
        $this->resultCallback = $resultCallback;
        $this->parseConditions();
    }

    /**
     * 设置事务状态
     *
     * @param bool $trans 是否开启事务
     *
     * @return bool
     */
    private function setTrans($trans)
    {
        if (boolval($trans)) {
            array_push($this->trans, $trans);
        } elseif (count($this->trans) > 0) {
            array_pop($this->trans);
        } else {
            return false;
        }
        return true;
    }

    /**
     * 设置调试开关
     *
     * @param int $debugType 调试
     *
     * @return $this
     */
    public function setDebugType($debugType)
    {
        $this->conditions['debug'] = intval($debugType);
        return $this;
    }

    /**
     * 解析参数条件
     */
    private function parseConditions()
    {
        if ($this->conditions['trans'] == true) {
            $this->startTrans();
        }
        try {
            $callback = $this->handleCallback;
            $result = $callback($this);
            $this->getUserConvertResult($result);
        } catch (Exception $e) {
            if ($e instanceof FunctionException) {
                $this->getUserConvertResult($e->getBaseResult());
            } else {
                $this->returnFailError($e, $this->conditions['log'][1] . "异常:" . $e->getMessage());
            }
        }
    }

    /**
     * 获取用户转换结果
     *
     * @param BaseResult $result
     */
    private function getUserConvertResult(BaseResult $result)
    {
        if (!is_null($this->resultCallback)) {
            $callback = $this->resultCallback;
            $this->endResult = $callback($result);
        } else {
            $this->endResult = $result;
        }
    }

    /**
     * 写入日志
     *
     * @param string|mixed $msg   消息
     * @param string       $level 级别
     * @param null         $tag   标识
     *
     * @return $this
     */
    public function log($msg, $level = 'debug', $tag = null)
    {
        $level = strtolower($level);
        if ($level != null && intval($this->conditions['debug']) > 0) {
            $model = $this->conditions['log'][0];
            $tag = is_null($tag) ? $this->conditions['log'][1] : $tag;
            $this->driver::log($msg, $level, $tag, $model);
        }
        return $this;
    }

    /**
     * 返回错误信息
     *
     * @param string     $message  消息
     * @param int|string $code     错误码
     * @param string     $logLevel 日志级别
     */
    public function returnFail($message = 'FAIL', $code = 400, $logLevel = 'error')
    {
        $this->rollback();
        $result = BaseResult::instance([$code, $message]);
        $this->log($result, $logLevel);
        $e = new FunctionException();
        $e->setBaseResult($result);
        throw $e;
    }

    /**
     * 返回错误信息
     *
     * @param Exception  $e       异常信息
     * @param string     $message 消息
     * @param int|string $code    错误码
     * @param int        $endLine 调用过程截止倒数行号
     *
     * @throws
     */
    private function returnFailError(Exception $e, $message = 'FAIL', $code = 400, $endLine = 0)
    {
        if ($this->conditions['trans'] == true && $this->setTrans(false)) {
            $this->driver::rollback();
        }
        $result = BaseResult::instance([$code, $message]);
        $errorInfo = 'file:' . $e->getFile() . ' line:' . $e->getLine() . ' message:' . $e->getMessage();
        $errorTrace = array();
        $traces = $e->getTrace();
        for ($i = 0; $i < (count($traces) - $endLine); $i++) {
            $v = $traces[$i];
            $errorMsg = 'file:' . (isset($v['file']) ? $v['file'] : 'null');
            $errorMsg .= ' line:' . (isset($v['line']) ? $v['line'] : 'null');
            $errorMsg .= ' function:' . (isset($v['function']) ? $v['function'] : 'null');
            array_push($errorTrace, $errorMsg);
        }
        $this->log(['error_info' => $errorInfo, 'error_trace' => $errorTrace, 'result' => $result], 'error');
        $result->setSource($e);
        $this->endResult = $result;
        if (intval($this->conditions['debug']) > 1) {
            throw $e;
        }
    }

    /**
     * 创建工厂
     *
     * @param callable(FunctionFactory) $handleCallback 处理回调
     * @param callable(BaseResult) $resultCallback 结果回调
     * @param array $condition 创建工厂参数
     *
     * @return BaseResult
     * @throws Exception
     */
    public static function create(callable $handleCallback, $resultCallback = null, array $condition = [])
    {
        $factory = new FunctionFactory($handleCallback, $resultCallback, $condition);
        return $factory->getEndResult();
    }

    /**
     * 返回最终结果
     *
     * @return BaseResult
     */
    public function getEndResult()
    {
        return $this->endResult;
    }

    /**
     * 检查错误并且返回错误
     *
     * @param bool       $failStatus 失败：true  无错误：false
     * @param string     $failMsg    失败信息
     * @param int|string $failCode   失败状态码
     * @param string     $logLevel   日志级别
     */
    public function checkFailReturn($failStatus, $failMsg = 'FAIL', $failCode = 400, $logLevel = 'error')
    {
        if ($failStatus == true) {
            $this->returnFail($failMsg, $failCode, $logLevel);
        }
    }

    /**
     * 返回成功信息
     *
     * @param string      $message  消息
     * @param mixed       $data     数据
     * @param int|string  $code     状态码
     * @param string|null $logLevel 日志级别
     *
     * @return BaseResult
     */
    public function returnSuccess($data = [], $message = 'OK', $code = 200, $logLevel = null)
    {
        $this->commit();
        $result = BaseResult::instance([$code, $message, $data]);
        $this->log($result, $logLevel);
        return $result;
    }

    /**
     * 事务保存并开启一个新事务
     *
     * @param bool $all 是否全部
     */
    public function commitAndStartTrans($all = true)
    {
        $this->commit($all);
        $this->startTrans();
    }

    /**
     * 事务回滚并开启一个新事务
     *
     * @param bool $all 是否全部
     */
    public function rollbackAndStartTrans($all = true)
    {
        $this->rollback($all);
        $this->startTrans();
    }

    /**
     * 事务开启
     */
    public function startTrans()
    {
        $this->driver::startTrans();
        $this->setTrans(true);
    }

    /**
     * 事务回滚
     *
     * @param bool $all 是否全部
     */
    public function rollback($all = true)
    {
        if ($all) {
            while ($this->setTrans(false)) {
                $this->driver::rollback();
            }
        } elseif ($this->setTrans(false)) {
            $this->driver::rollback();
        }
    }

    /**
     * 事务提交
     *
     * @param bool $all 是否全部
     */
    public function commit($all = true)
    {
        if ($all) {
            while ($this->setTrans(false)) {
                $this->driver::commit();
            }
        } elseif ($this->setTrans(false)) {
            $this->driver::commit();
        }
    }
}
