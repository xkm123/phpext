<?php
namespace php_ext\eventbus;

use php_ext\eventbus\template\EventData;

/**
 * 事件驱动
 * Class EventBus
 * User: 邢可盟
 *
 * @package extlib\eventbus
 */
class EventBus
{
    /**
     * 事件驱动
     *
     * @var array
     */
    private static $events = array();

    /**
     * 触发一个事件
     *
     * @param string $name 事件名称
     * @param array  $data 数据
     *
     * @return EventData|null
     * @throws
     */
    public static function emit($name, $data = [])
    {
        /* @var $result \extlib\eventbus\template\EventData */
        $result = null;
        if (!empty(self::$events[$name])) {
            /* @var $event \extlib\eventbus\template\EventData */
            foreach (self::$events[$name] as $index => $event) {
                $event->setBaseData($data);
                if ($index == 0) {
                    $event->setSourceData($data);
                } else {
                    $event->setSourceData($result->getResultData());
                    $event->addEvent($result);
                }
                try {
                    $result = self::emitEvent($event);
                    if (!$result->isSuccess()) {
                        return $result;
                    }
                } catch (\Exception $e) {
                    return $event->setCode($event::FAIL)
                        ->setMessage("执行插件异常" . "(" . $index . "):" . $event->getClass() . "->" . $event->getAction())
                        ->setException($e);
                }
            }
        } else {
            $result = new EventData();
            $result->setSourceData($data);
            $result->setResultData($data);
        }
        return $result;
    }

    /**
     * 触发一个事件
     *
     * @param EventData $event
     *
     * @return  EventData
     * @throws
     */
    public static function emitEvent(EventData $event)
    {
        if (!class_exists($event->getClass())) {
            throw new \Exception("事件[" . $event->getName() . "]触发类不存在：" . $event->getClass());
        }
        $class = $event->getClass();
        $action = $event->getAction();
        if ($event->isStaticFunc()) {
            return $class::$action($event);
        } else {
            return (new $class())->$action($event);
        }
    }

    /**
     * 添加观察者
     *
     * @param EventData|array $event 事件
     */
    private static function watch($event)
    {
        if (is_array($event)) {
            $event = new EventData($event);
        }
        if (!isset(self::$events[$event->getName()])) {
            self::$events[$event->getName()] = array();
        }
        array_push(self::$events[$event->getName()], $event);
    }

    /**
     * 排序
     *
     * @param string|null $name 事件名称
     *
     * @return array
     */
    public static function sort($name = null)
    {
        if (!empty($name) && self::$events[$name]) {
            usort(self::$events[$name], function (EventData $e1, EventData $e2) {
                return $e1->getPriority() >= $e2->getPriority();
            });
        } else {
            foreach (self::$events as $key => $values) {
                usort(self::$events[$key], function (EventData $e1, EventData $e2) {
                    return $e1->getPriority() >= $e2->getPriority();
                });
            }
        }
        return self::$events;
    }

    /**
     * 导入配置
     *
     * @param array $events 事件集合
     */
    public static function import(array $events = [])
    {
        foreach ($events as $es) {
            foreach ($es as $event) {
                self::watch($event);
            }
        }
        self::sort();
    }

    /**
     * 导出配置
     *
     * @return array
     */
    public static function export()
    {
        $result = self::sort();
        foreach ($result as $k => $events) {
            foreach ($events as $ek => $ev) {
                /* @var $ev EventData */
                $result[$k][$ek] = $ev->toArray();
            }
        }
        return $result;
    }

    /**
     * 添加观察者
     *
     * @param array $events 事件集合
     */
    public static function batchWatch(array $events)
    {
        foreach ($events as $event) {
            self::watch($event);
        }
        self::sort();
    }
}
