<?php

declare(strict_types = 1);

namespace Rx\Scheduler;

use Rx\Disposable\SingleAssignmentDisposable;
use Rx\DisposableInterface;
use Rx\SchedulerInterface;

class ScheduledItem
{
    /**
     * @var SchedulerInterface
     */
    private $scheduler;

    /**
     * @var mixed
     */
    private $state;

    /**
     * @var (callable(SchedulerInterface, mixed): DisposableInterface)
     */
    private $action;

    /**
     * @var int
     */
    private $dueTime;

    /**
     * @var callable
     */
    private $comparer;

    /**
     * @var SingleAssignmentDisposable
     */
    private $disposable;

    /**
     * @param mixed $state
     * @param (callable(SchedulerInterface, mixed): DisposableInterface) $action
     * @param (callable(int, int): int) $comparer
     */
    public function __construct(SchedulerInterface $scheduler, $state, callable $action, int $dueTime, callable $comparer = null)
    {
        $this->scheduler  = $scheduler;
        $this->state      = $state;
        $this->action     = $action;
        $this->dueTime    = $dueTime;
        $this->comparer   = $comparer ?: function (int $a, int $b) {
            return $a - $b;
        };
        $this->disposable = new SingleAssignmentDisposable();
    }

    /**
     * @return void
     */
    public function invoke()
    {
        $this->disposable->setDisposable($this->invokeCore());
    }

    /**
     * @param ScheduledItem $other
     * @return int
     */
    public function compareTo(ScheduledItem $other)
    {
        $comparer = $this->comparer;
        return $comparer($this->dueTime, $other->dueTime);
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return $this->disposable->isDisposed();
    }

    public function invokeCore(): DisposableInterface
    {
        $action = $this->action;
        return $action($this->scheduler, $this->state);
    }

    /**
     * @return int
     */
    public function getDueTime()
    {
        return $this->dueTime;
    }

    /**
     * @return SingleAssignmentDisposable
     */
    public function getDisposable()
    {
        return $this->disposable;
    }
}
