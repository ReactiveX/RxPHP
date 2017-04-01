<?php

declare(strict_types = 1);

namespace Rx;

use Rx\Scheduler\ImmediateScheduler;

final class Scheduler
{
    private static $default;
    private static $async;
    private static $immediate;
    private static $defaultFactory;
    private static $asyncFactory;

    public static function getDefault(): SchedulerInterface
    {
        if (static::$default) {
            return static::$default;
        }

        if (static::$defaultFactory === null) {
            throw new \Exception('Please set a default scheduler factory');
        }

        static::$default = call_user_func(static::$defaultFactory);

        return static::$default;
    }

    public static function setDefaultFactory(callable $factory)
    {
        if (static::$default !== null) {
            throw new \Exception("The default factory can not be set after the scheduler has been created");
        }

        static::$defaultFactory = $factory;
    }

    public static function getAsync(): AsyncSchedulerInterface
    {
        if (static::$async) {
            return static::$async;
        }

        if (static::$asyncFactory === null && static::getDefault() instanceof AsyncSchedulerInterface) {
            static::$async = static::$default;
            return static::$async;
        }

        if (static::$asyncFactory === null) {
            throw new \Exception('Please set an async scheduler factory');
        }

        static::$async = call_user_func(static::$asyncFactory);

        return static::$async;
    }

    public static function setAsyncFactory(callable $factory)
    {
        if (static::$async !== null) {
            throw new \Exception("The async factory can not be set after the scheduler has been created");
        }

        static::$asyncFactory = $factory;
    }

    public static function getImmediate(): ImmediateScheduler
    {
        if (!static::$immediate) {
            static::$immediate = new ImmediateScheduler();
        }
        return self::$immediate;
    }
}
