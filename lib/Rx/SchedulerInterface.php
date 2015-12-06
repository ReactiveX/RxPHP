<?php

namespace Rx;

interface SchedulerInterface
{
    /**
     * @param callable $action
     *
     * @return DisposableInterface
     */
    public function schedule(callable $action);

    /**
     * @param callable $action
     *
     * @return DisposableInterface
     */
    public function scheduleRecursive(callable $action);

    /**
     * Gets the current representation of now for the scheduler
     *
     * @return mixed
     */
    public function now();
}
