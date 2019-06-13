<?php
namespace php_ext\func;

use php_ext\BaseResult;
use Throwable;

/**
 * 方法异常类
 * Class FunctionException
 *
 * @package php_ext\func
 */
class FunctionException extends \RuntimeException
{
    /**
     * @var BaseResult
     */
    private $baseResult;

    /**
     * FunctionException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return BaseResult
     */
    public function getBaseResult()
    {
        return $this->baseResult;
    }

    /**
     * @param BaseResult $baseResult
     */
    public function setBaseResult($baseResult)
    {
        $this->baseResult = $baseResult;
    }
}
