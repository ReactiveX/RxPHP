<?php

namespace Rx;

interface SchedulerInterface
{
    /**
     * @param mixed $action A callable
     *
     * @return DisposableInterface
     */
    public function schedule($action);

    /**
     * @param mixed $action A callable
     *
     * @return DisposableInterface
     */
    public function scheduleRecursive($action);

    /**
     * Gets the current representation of now for the scheduler
     *
     * @return mixed
     */
    public function now();
}
