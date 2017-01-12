<?php

declare(strict_types = 1);

namespace Rx\Scheduler;

use Rx\Disposable\SingleAssignmentDisposable;

class ScheduledItem
{
    private $scheduler;
    private $state;
    private $action;
    private $dueTime;
    private $comparer;

    public function __construct($scheduler, $state, $action, $dueTime, $comparer = null)
    {
        $this->scheduler  = $scheduler;
        $this->state      = $state;
        $this->action     = $action;
        $this->dueTime    = $dueTime;
        $this->comparer   = $comparer ?: function ($a, $b) {
            return $a - $b;
        };
        $this->disposable = new SingleAssignmentDisposable();
    }

    public function invoke()
    {
        $this->disposable->setDisposable($this->invokeCore());
    }

    public function compareTo(ScheduledItem $other)
    {
        $comparer = $this->comparer;
        return $comparer($this->dueTime, $other->dueTime);
    }

    public function isCancelled()
    {
        return $this->disposable->isDisposed();
    }

    public function invokeCore()
    {
        $action = $this->action;
        return $action($this->scheduler, $this->state);
    }

    public function getDueTime()
    {
        return $this->dueTime;
    }

    public function getDisposable()
    {
        return $this->disposable;
    }
}
