<?php
namespace php_ext;

use DateTimeZone;

/**
 * 时间类
 * Class DateTime
 *
 * @package php_ext
 */
class DateTime
{
    /**
     * 时区
     */
    const PROPERTY_TIMEZONE = 'timezone';
    /**
     * 时间戳
     */
    const PROPERTY_TIMESTAMP = 'timestamp';
    /**
     * 当前DateTime对象
     */
    const PROPERTY_DATETIME = 'datetime';
    /**
     * @var \DateTime
     */
    private $initTime;

    /**
     * 生成一个DateTime对象
     *
     * @param string|int $time     时间
     * @param string     $timezone 时区
     *
     * @return DateTime
     */
    public static function make($time = 'now', $timezone = 'Asia/Shanghai')
    {
        return new self($time, $timezone);
    }

    /**
     * 拷贝当前时间
     *
     * @return DateTime
     */
    public function copy()
    {
        return new DateTime($this->format(), $this->getProperty(self::PROPERTY_TIMEZONE));
    }

    /**
     * DateTime constructor.
     *
     * @param string|int          $time     时间
     * @param string|DateTimeZone $timezone 时区
     */
    public function __construct($time = 'now', $timezone = 'Asia/Shanghai')
    {
        $this->setDateTime($time, $timezone);
    }

    /**
     * 增加秒
     *
     * @param int $second 秒
     *
     * @return $this
     */
    public function addSecond($second)
    {
        return $this->modify(sprintf('%s%s second', $second > 0 ? '+' : '', $second));
    }

    /**
     * 增加分
     *
     * @param int $minute 分
     *
     * @return $this
     */
    public function addMinute($minute)
    {
        return $this->modify(sprintf('%s%s minute', $minute > 0 ? '+' : '', $minute));
    }

    /**
     * 增加小时
     *
     * @param int $hour 小时
     *
     * @return $this
     */
    public function addHour($hour)
    {
        return $this->modify(sprintf('%s%s hour', $hour > 0 ? '+' : '', $hour));
    }

    /**
     * 增加天数
     *
     * @param int $day 天数
     *
     * @return $this
     */
    public function addDay($day)
    {
        return $this->modify(sprintf('%s%s day', $day > 0 ? '+' : '', $day));
    }

    /**
     * 增加周
     *
     * @param int $week 周
     *
     * @return $this
     */
    public function addWeek($week)
    {
        return $this->modify(sprintf('%s%s week', $week > 0 ? '+' : '', $week));
    }

    /**
     * 增加月
     *
     * @param int $month 月
     *
     * @return $this
     */
    public function addMonth($month)
    {
        return $this->modify(sprintf('%s%s month', $month > 0 ? '+' : '', $month));
    }

    /**
     * 增加年
     *
     * @param int $year 年
     *
     * @return $this
     */
    public function addYear($year)
    {
        return $this->modify(sprintf('%s%s year', $year > 0 ? '+' : '', $year));
    }

    /**
     * 添加时间
     *
     * @param array $property 属性 [year,month,week,day,hour,minute,second] 例子：['year'=>1]
     *
     * @return $this
     */
    public function add(array $property)
    {
        $cmd = '';
        foreach ($property as $k => $v) {
            $cmd = sprintf('%s%s %s ', $v > 0 ? '+' : '', $v, strtolower($k));
        }
        $cmd = trim($cmd);
        $this->initTime->modify($cmd);
        return $this;
    }

    /**
     * 修改
     *
     * @param string $property 属性
     *
     * @return $this
     */
    public function modify($property)
    {
        $this->initTime->modify($property);
        return $this;
    }

    /**
     * 设置时区
     *
     * @param string $timezone 时区
     *
     * @return $this
     */
    public function setTimeZone($timezone)
    {
        $this->initTime->setTimezone(new \DateTimeZone($timezone));
        return $this;
    }

    /**
     * 设置时间
     *
     * @param string|int $time     时间
     * @param string     $timezone 时区
     *
     * @return $this
     */
    public function setDateTime($time = 'now', $timezone = 'Asia/Shanghai')
    {
        $time = is_numeric($time) ? date('Y-m-d H:i:s', $time) : $time;
        $timezone = is_string($timezone) ? new DateTimeZone($timezone) : $timezone;
        $this->initTime = new \DateTime($time, $timezone);
        return $this;
    }

    /**
     * 设置日期
     *
     * @param int $year  年
     * @param int $month 月
     * @param int $day   天
     *
     * @return $this
     */
    public function setDate($year, $month = 1, $day = 1)
    {
        $year = $year >= 0 ? $year : date('Y', $this->getTimestamp());
        $month = $month > 0 ? $month : date('m', $this->getTimestamp());
        $day = $day > 0 ? $day : date('d', $this->getTimestamp());
        $this->initTime->setDate($year, $month, $day);
        return $this;
    }

    /**
     * 设置时间
     *
     * @param int $hour         小时
     * @param int $minute       分钟
     * @param int $second       秒
     * @param int $microseconds 毫秒
     *
     * @return $this
     */
    public function setTime($hour = 0, $minute = 0, $second = 0, $microseconds = 0)
    {
        $hour = $hour >= 0 ? $hour : date('H', $this->getTimestamp());
        $minute = $minute >= 0 ? $minute : date('i', $this->getTimestamp());
        $second = $second >= 0 ? $second : date('s', $this->getTimestamp());
        $microseconds = $microseconds >= 0 ? $microseconds : date('u', $this->getTimestamp());
        $this->initTime->setTime($hour, $minute, $second, $microseconds);
        return $this;
    }

    /**
     * 获取当前时间属性
     *
     * @param string   $property 属性
     * @param DateTime $dateTime 时间对象
     *
     * @return mixed
     */
    public function getProperty($property = self::PROPERTY_DATETIME, $dateTime = null)
    {
        $dateTime = $dateTime != null ? $dateTime : $this->initTime;
        switch ($property) {
            case self::PROPERTY_TIMEZONE:
                return $dateTime->getTimezone();
            case self::PROPERTY_TIMESTAMP:
                return $dateTime->getTimestamp();
            case self::PROPERTY_DATETIME:
                return $dateTime;
        }
        $time = $dateTime->getTimestamp();
        return date($property, $time);
    }

    /**
     * 比较时间
     *
     * @param DateTime|\DateTime|int $dateTime 时间
     *
     * @return int
     */
    public function diff($dateTime)
    {
        if (is_int($dateTime)) {
            return $this->initTime->getTimestamp() - $dateTime;
        }
        return $this->initTime->getTimestamp() - $dateTime->getTimestamp();
    }

    /**
     * 获取当前时间戳
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->initTime->getTimestamp();
    }

    /**
     * 格式化时间
     *
     * @param string $format 时间格式
     *
     * @return string
     */
    public function format($format = 'Y-m-d H:i:s')
    {
        return $this->initTime->format($format);
    }

    /**
     * 获取季度时间
     *
     * @param int           $option   选项 0：当前季度 1：当前季度月初月份  2：当前月在当前季度的位置 3：季度末月份
     * @param DateTime|null $dateTime 时间
     *
     * @return int
     */
    public function getQuarterTime($option = 0, $dateTime = null)
    {
        $dateTime = $dateTime != null ? $dateTime : $this->initTime;
        $month = date('n', $dateTime->getTimestamp());
        $season = ceil($month / 3);
        switch ($option) {
            case 1:
                return ($season - 1) * 3 + 1;
            case 2:
                return $month - ($season - 1) * 3 - 1;
            case 3:
                return $season * 3;
        }
        return $season;
    }

    /**
     * 转成string
     *
     * @return string
     */
    public function __toString()
    {
        return self::format();
    }
}
/*
d -一个月中的第几天（从 01 到 31）
D - 星期几的文本表示（用三个字母表示）
j - 一个月中的第几天，不带前导零（1 到 31）
l（'L' 的小写形式） - 星期几的完整的文本表示
N - 星期几的 ISO - 8601 数字格式表示（1表示Monday[星期一]，7表示Sunday[星期日]）
S - 一个月中的第几天的英语序数后缀（2 个字符：st、nd、rd 或 th。与 j 搭配使用）
w - 星期几的数字表示（0 表示 Sunday[星期日]，6 表示 Saturday[星期六]）
z - 一年中的第几天（从 0 到 365）
W - 用 ISO - 8601 数字格式表示一年中的星期数字（每周从 Monday[星期一]开始）
F - 月份的完整的文本表示（January[一月份] 到 December[十二月份]）
m - 月份的数字表示（从 01 到 12）
M - 月份的短文本表示（用三个字母表示）
n - 月份的数字表示，不带前导零（1 到 12）
t - 给定月份中包含的天数
L - 是否是闰年（如果是闰年则为 1，否则为 0）
o - ISO - 8601 标准下的年份数字
Y - 年份的四位数表示
y - 年份的两位数表示
a - 小写形式表示：am 或 pm
A - 大写形式表示：AM 或 PM
B - Swatch Internet Time（000 到 999）
g - 12 小时制，不带前导零（1 到 12）
G - 24 小时制，不带前导零（0 到 23）
h - 12 小时制，带前导零（01 到 12）
H - 24 小时制，带前导零（00 到 23）
i - 分，带前导零（00 到 59）
s - 秒，带前导零（00 到 59）
u - 微秒（PHP 5.2.2 中新增的）
e - 时区标识符（例如：UTC、GMT、Atlantic / Azores）
I（i 的大写形式） - 日期是否是在夏令时（如果是夏令时则为 1，否则为 0）
O - 格林威治时间（GMT）的差值，单位是小时（实例： + 0100）
P - 格林威治时间（GMT）的差值，单位是 hours:minutes（PHP 5.1.3 中新增的）
T - 时区的简写（实例：EST、MDT）
Z - 以秒为单位的时区偏移量。UTC 以西时区的偏移量为负数（ - 43200 到 50400）
c - ISO - 8601 标准的日期（例如 2013 - 05 - 05T16:34:42 + 00:00）
r - RFC 2822 格式的日期（例如 Fri, 12 Apr 2013 12:01:05 + 0200）
U - 自 Unix 纪元（January 1 1970 00:00:00 GMT）以来经过的秒数
DATE_ATOM - Atom（例如：2013-04-12T15:52:01+00:00）
DATE_COOKIE - HTTP Cookies（例如：Friday, 12-Apr-13 15:52:01 UTC）
DATE_ISO8601 - ISO-8601（例如：2013-04-12T15:52:01+0000）
DATE_RFC822 - RFC 822（例如：Fri, 12 Apr 13 15:52:01 +0000）
DATE_RFC850 - RFC 850（例如：Friday, 12-Apr-13 15:52:01 UTC）
DATE_RFC1036 - RFC 1036（例如：Fri, 12 Apr 13 15:52:01 +0000）
DATE_RFC1123 - RFC 1123（例如：Fri, 12 Apr 2013 15:52:01 +0000）
DATE_RFC2822 - RFC 2822（Fri, 12 Apr 2013 15:52:01 +0000）
DATE_RFC3339 - 与 DATE_ATOM 相同（从 PHP 5.1.3 开始）
DATE_RSS - RSS（Fri, 12 Aug 2013 15:52:01 +0000）
DATE_W3C - 万维网联盟（例如：2013-04-12T15:52:01+00:00）
 */
