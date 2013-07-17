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
}
