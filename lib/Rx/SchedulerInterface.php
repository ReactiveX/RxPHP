<?php

namespace Rx;

interface SchedulerInterface
{
    /**
     * @param callable $action
     * @param $delay
     *
     * @return DisposableInterface
     */
    public function schedule(callable $action, $delay = 0);

    /**
     * @param callable $action
     *
     * @return DisposableInterface
     */
    public function scheduleRecursive(callable $action);

    /**
     * @param callable $action
     * @param int $delay
     * @param $period
     *
     * @return mixed
     */
    public function schedulePeriodic(callable $action, $delay, $period);

    /**
     * Gets the current representation of now for the scheduler
     *
     * @return mixed
     */
    public function now();
}
