<?php

declare(strict_types = 1);

namespace Rx;

use Rx\Scheduler\ImmediateScheduler;

final class Scheduler
{
    /**
     * @var null|SchedulerInterface|AsyncSchedulerInterface
     */
    private static $default;

    /**
     * @var ?AsyncSchedulerInterface
     */
    private static $async;

    /**
     * @var ?ImmediateScheduler
     */
    private static $immediate;

    /**
     * @var (callable(): SchedulerInterface)
     */
    private static $defaultFactory;

    /**
     * @var (callable(): AsyncSchedulerInterface)
     */
    private static $asyncFactory;

    /**
     * @return SchedulerInterface|AsyncSchedulerInterface
     */
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

    /**
     * @param (callable(): SchedulerInterface) $factory
     * @return void
     */
    public static function setDefaultFactory(callable $factory)
    {
        if (static::$default !== null) {
            throw new \Exception("The default factory can not be set after the scheduler has been created");
        }

        static::$defaultFactory = $factory;
    }

    public static function getAsync(): AsyncSchedulerInterface
    {
        if (static::$async instanceof AsyncSchedulerInterface) {
            return static::$async;
        }

        if (static::$asyncFactory === null && static::getDefault() instanceof AsyncSchedulerInterface) {
            assert(static::$default instanceof AsyncSchedulerInterface);
            static::$async = static::$default;
            assert(static::$async instanceof AsyncSchedulerInterface);
            return static::$async;
        }

        if (static::$asyncFactory === null) {
            throw new \Exception('Please set an async scheduler factory');
        }

        static::$async = call_user_func(static::$asyncFactory);

        return static::$async;
    }

    /**
     * @param (callable(): AsyncSchedulerInterface) $factory
     * @return void
     */
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

        assert(self::$immediate instanceof ImmediateScheduler);
        return self::$immediate;
    }
}
