<?php

declare(strict_types = 1);

namespace Rx;

interface SchedulerInterface
{
    /**
     * @param $delay
     *
     */
    public function schedule(callable $action, $delay = 0): DisposableInterface;

    
    public function scheduleRecursive(callable $action): DisposableInterface;

    /**
     * @param int $delay
     * @param $period
     *
     */
    public function schedulePeriodic(callable $action, $delay, $period): DisposableInterface;

    /**
     * Gets the current representation of now for the scheduler
     */
    public function now(): int;
}
