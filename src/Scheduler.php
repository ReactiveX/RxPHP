<?php

namespace Rx;

use Rx\Scheduler\EventLoopScheduler;
use Rx\Scheduler\ImmediateScheduler;

class Scheduler
{
    private static $default;
    private static $async;
    private static $immediate;

    public static function getDefault(): SchedulerInterface
    {
        if (!static::$default) {
            static::$default = new EventLoopScheduler();
        }

        return static::$default;
    }

    public static function setDefault(SchedulerInterface $scheduler)
    {
        static::$default = $scheduler;
    }

    public static function getAsync(): SchedulerInterface
    {
        if (!static::$async) {
            static::$async = new EventLoopScheduler();
        }
        return self::$async;
    }

    public static function getImmediate(): ImmediateScheduler
    {
        if (!static::$immediate) {
            static::$immediate = new ImmediateScheduler();
        }
        return self::$immediate;
    }

    public static function setAsync($async)
    {
        self::$async = $async;
    }

    public static function setImmediate($immediate)
    {
        self::$immediate = $immediate;
    }
}
