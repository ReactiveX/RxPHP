<?php

namespace Rx;

use Rx\Scheduler\ImmediateScheduler;

class Scheduler
{
    private static $scheduler;

    /**
     * @return SchedulerInterface
     */
    public static function getDefault()
    {
        if (!static::$scheduler) {
            static::$scheduler = new ImmediateScheduler();
        }

        return static::$scheduler;
    }

    /**
     * @param SchedulerInterface $scheduler
     */
    public static function setDefault(SchedulerInterface $scheduler)
    {
        static::$scheduler = $scheduler;
    }
}
