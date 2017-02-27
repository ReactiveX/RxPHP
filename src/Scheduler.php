<?php

declare(strict_types = 1);

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
        if (static::$default) {
            return static::$default;
        }

        throw new \Exception(
            "Please set a default scheduler (for react: Scheduler::setDefault(new EventLoopScheduler(\$loop));"
        );
    }

    public static function setDefault(SchedulerInterface $scheduler)
    {
        if (static::$default !== null) {
            throw new \Exception("Scheduler can only be set once.");
        }

        static::$default = $scheduler;
    }

    public static function getAsync(): AsyncSchedulerInterface
    {
        if (static::$async) {
            return static::$async;
        }

        if (static::$default instanceof AsyncSchedulerInterface) {
            static::$async = static::$default;

            return static::$async;
        }

        throw new \Exception(
            "Please set an async scheduler (for react: Scheduler::setAsync(new EventLoopScheduler(\$loop));"
        );
    }

    public static function getImmediate(): ImmediateScheduler
    {
        if (!static::$immediate) {
            static::$immediate = new ImmediateScheduler();
        }
        return self::$immediate;
    }

    public static function setAsync(AsyncSchedulerInterface $async)
    {
        if (static::$async !== null) {
            throw new \Exception("Scheduler can only be set once.");
        }
        self::$async = $async;
    }

    public static function setImmediate(SchedulerInterface $immediate)
    {
        if (static::$immediate !== null) {
            throw new \Exception("Scheduler can only be set once.");
        }
        self::$immediate = $immediate;
    }
}
