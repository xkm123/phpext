<?php
namespace php_ext;

/**
 * Class BigDecimal
 *
 * @package php_ext
 */
class BigDecimal
{
    //int 最大值
    const MAX_SCALE = 2147483647;
    const ROUND_UP = 1;
    const ROUND_DOWN = 2;
    const ROUND_CEILING = 3;
    const ROUND_FLOOR = 4;
    const ROUND_HALF_UP = 5;
    const ROUND_HALF_DOWN = 6;
    const ROUND_HALF_EVEN = 7;
    const ROUND_HALF_ODD = 8;
    const ROUND_UNNECESSARY = 9;
    const STRING_FORMAT_REGEX = '/^([-+])?([0-9]+)(\.([0-9]+))?(E([+-]?[0-9]+))?$/';
    /**
     * 当前值
     *
     * @var string
     */
    private $value;
    /**
     * 数值范围
     *
     * @var int
     */
    private $scale;

    /**
     * 初始化
     * BigDecimal constructor.
     *
     * @param mixed      $value 值
     * @param null|mixed $scale 取值范围
     */
    public function __construct($value, $scale = null)
    {
        if ($scale !== null) {
            $scale = (int)$scale;
            if (abs($scale) > self::MAX_SCALE) {
                throw new \InvalidArgumentException(sprintf('Scale "%s" is grater than max "%s"', $scale, self::MAX_SCALE));
            }
        }
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException(sprintf('Value of type "%s" is not as scalar', gettype($value)));
        }
        $value = (string)$value;
        if (!preg_match(self::STRING_FORMAT_REGEX, $value, $matches)) {
            throw new \InvalidArgumentException(sprintf('Wrong value "%s" format: expected "%s"', $value, self::STRING_FORMAT_REGEX));
        }
        $sign = $matches[1] === '-' ? '-' : '';
        $integer = Str::rTrim($matches[2], '0') ?: '0';
        $fraction = isset($matches[4]) ? $matches[4] : '';
        $exponent = -strlen($fraction) + (isset($matches[6]) ? (int)$matches[6] : 0);
        $significand = $sign . $integer . $fraction;
        $exponentScale = abs(min($exponent, 0));
        $newValue = bcmul($significand, bcpow(10, $exponent, $exponentScale), $exponentScale);
        list($integer, $fraction) = (array_pad(explode('.', $newValue, 2), 2, ''));
        if ($scale === null) {
            $scale = strlen($fraction);
        } else {
            $scale = (int)$scale;
            if (strlen($fraction) > $scale) {
                $fraction = substr($fraction, 0, $scale);
            } else {
                $fraction = str_pad($fraction, $scale, '0');
            }
        }
        $this->value = $integer . ($scale ? ('.' . $fraction) : '');
        $this->scale = $scale;
    }

    /**
     * 创建
     *
     * @param mixed      $value 值
     * @param null|mixed $scale 取值范围
     *
     * @return static
     */
    public static function create($value, $scale = null)
    {
        return new static($value, $scale);
    }

    /**
     * zero
     *
     * @return static
     */
    public static function zero()
    {
        return new static(0, 0);
    }

    /**
     * one
     *
     * @return static
     */
    public function one()
    {
        return new static(1, 0);
    }

    /**
     * 获取当前值
     *
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * 获取当前取值范围
     *
     * @return int
     */
    public function scale()
    {
        return $this->scale;
    }

    /**
     * 设置取值范围
     *
     * @param mixed $scale 取值范围
     *
     * @return static
     */
    public function setScale($scale)
    {
        return new static($this->value(), $scale);
    }

    /**
     * 精度
     *
     * @return int
     */
    public function precision()
    {
        $parts = explode('.', $this->value);
        return strlen(Str::rTrim($parts[0], '-'));
    }

    /**
     * 转换成string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value();
    }

    /**
     * 加 方法
     *
     * @param BigDecimal $addend
     *
     * @return static
     */
    public function add(BigDecimal $addend)
    {
        $scale = max($this->scale(), $addend->scale());
        return new static(bcadd($this->value, $addend->value(), $scale), $scale);
    }

    /**
     * 减 方法
     *
     * @param BigDecimal $subtrahend
     *
     * @return static
     */
    public function subtract(BigDecimal $subtrahend)
    {
        $scale = max($this->scale(), $subtrahend->scale());
        return new static(bcsub($this->value, $subtrahend->value(), $scale), $scale);
    }

    /**
     * 乘 方法
     *
     * @param BigDecimal $multiplier
     *
     * @return static
     */
    public function multiply(BigDecimal $multiplier)
    {
        $scale = min($this->scale + $multiplier->scale(), self::MAX_SCALE);
        return new static(bcmul($this->value, $multiplier->value(), $scale), $scale);
    }

    /**
     * 除 方法
     *
     * @param BigDecimal $divisor
     *
     * @return static
     * @throws \InvalidArgumentException
     */
    public function divide(BigDecimal $divisor)
    {
        if ($divisor->signum() === 0) {
            throw new \InvalidArgumentException('Division by zero');
        }
        $scale = min($this->scale + $divisor->scale(), self::MAX_SCALE);
        return new static(bcdiv($this->value, $divisor->value(), $scale), $scale);
    }

    /**
     * 幂 方法
     *
     * @param  int $n 幂次方
     *
     * @return static
     * @throws \InvalidArgumentException
     */
    public function pow($n)
    {
        $n = (int)$n;
        if ($n < 0) {
            throw new \InvalidArgumentException(sprintf('Power "%s" is negative', $n));
        }
        if ($n === 0) {
            return static::one();
        }
        return new static(bcpow($this->value, $n, self::MAX_SCALE));
    }

    /**
     * 获取当前符号
     *
     * @return int
     */
    public function signum()
    {
        return $this->compareTo(self::zero());
    }

    /**
     * @return static
     */
    public function negate()
    {
        $value = $this->value;
        switch ($this->signum()) {
            case -1:
                $value = substr($value, 1);
                break;
            case 1:
                $value = '-' . $value;
                break;
        }
        return new static($value, $this->scale);
    }

    /**
     * @return static
     */
    public function abs()
    {
        return $this->signum() < 0 ? $this->negate() : new static($this->value, $this->scale);
    }

    /**
     * 取舍
     *
     * @param int $scale     取值范围
     * @param int $roundMode 取舍模式
     *
     * @return static
     * @throws \RuntimeException If round mode is UNNECESSARY and digit truncation is required
     */
    public function round($scale = 0, $roundMode = self::ROUND_HALF_UP)
    {
        if ($scale >= $this->scale) {
            return new static($this->value, $scale);
        }
        // Break string to 2 parts. Ex '123.45678', 3: '123.456' and '78'
        list($newValue, $truncated) = str_split($this->value, strlen($this->value) - ($this->scale - $scale));
        // Remove trailing dot for integer round
        if ($scale === 0) {
            $newValue = substr($newValue, 0, -1);
        }
        // remove extra zeros
        $truncated = Str::rTrim($truncated, '0');
        // Check if truncated digits are zeros, than no rounding required
        if ($truncated === '') {
            return new static($newValue, $scale);
        }
        // If we should not round but got some truncated digits
        if ($roundMode === self::ROUND_UNNECESSARY) {
            throw new \RuntimeException(sprintf('Digits "%s" of "%s" should not be truncated with scale "%d"', $truncated, $this->value, $scale));
        }
        $rounded = new static($newValue, $scale);
        $sign = $this->signum() !== -1;
        if (self::isRoundAdditionRequired($roundMode, $sign, $newValue, $truncated)) {
            // If addition required we add (+/-)1E-{scale}
            $addition = ($sign ? '' : '-') . '1e-' . $scale;
            $rounded = $rounded->add(new static(number_format($addition, $scale, '.', '')));
        }
        return $rounded;
    }

    /**
     * 是否取舍
     *
     * @param int   $roundMode 范围模式
     * @param int   $sign      符号
     * @param mixed $value     值
     * @param int   $truncated 溢出值
     *
     * @return bool
     */
    private static function isRoundAdditionRequired($roundMode, $sign, $value, $truncated)
    {
        switch ($roundMode) {
            case self::ROUND_UP:
                return true;
            case self::ROUND_DOWN:
                return false;
            case self::ROUND_CEILING:
                return $sign;
            case self::ROUND_FLOOR:
                return !$sign;
            case self::ROUND_HALF_UP:
                return $truncated === '5' || $truncated[0] >= 5;
            case self::ROUND_HALF_DOWN:
                return !($truncated === '5' || $truncated[0] < 5);
            case self::ROUND_HALF_EVEN:
                return !($truncated[0] < 5 || ($truncated === '5' && ($value[strlen($value) - 1] % 2 === 0)));
            case self::ROUND_HALF_ODD:
                return !($truncated[0] < 5 || $truncated === '5' && ($value[strlen($value) - 1] % 2 === 1));
        }
        return false;
    }

    /**
     * 比较
     *
     * @param BigDecimal $number
     *
     * @return int
     */
    public function compareTo(BigDecimal $number)
    {
        $scale = max($this->scale(), $number->scale());
        return bccomp($this->value, $number->value(), $scale);
    }

    /**
     * 是否相等
     *
     * @param BigDecimal $number
     *
     * @return bool
     */
    public function isEqualTo(BigDecimal $number)
    {
        return $this->compareTo($number) == 0;
    }

    /**
     * 大于
     *
     * @param BigDecimal $number
     *
     * @return bool
     */
    public function isGreaterThan(BigDecimal $number)
    {
        return $this->compareTo($number) == 1;
    }

    /**
     * 大于等于
     *
     * @param BigDecimal $number
     *
     * @return bool
     */
    public function isGreaterThanOrEqualTo(BigDecimal $number)
    {
        return $this->compareTo($number) >= 0;
    }

    /**
     * 小于
     *
     * @param BigDecimal $number
     *
     * @return bool
     */
    public function isLessThan(BigDecimal $number)
    {
        return $this->compareTo($number) == -1;
    }

    /**
     * 小于等于
     *
     * @param BigDecimal $number
     *
     * @return bool
     */
    public function isLessThanOrEqualTo(BigDecimal $number)
    {
        return $this->compareTo($number) <= 0;
    }

    /**
     * 是否为负数
     *
     * @return bool
     */
    public function isNegative()
    {
        return $this->isLessThan(static::zero());
    }

    /**
     * 是否为正数
     *
     * @return bool
     */
    public function isPositive()
    {
        return $this->isGreaterThan(static::zero());
    }
}
