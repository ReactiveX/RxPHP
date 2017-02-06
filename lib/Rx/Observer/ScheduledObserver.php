<?php

namespace Rx\Observer;

use Exception;
use Rx\Disposable\SerialDisposable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class ScheduledObserver extends AbstractObserver
{
    /** @var SchedulerInterface */
    private $scheduler;

    /** @var ObserverInterface */
    private $observer;

    /** @var bool */
    public $isAcquired = false;

    /** @var bool */
    private $hasFaulted = false;

    /** @var \Closure[] */
    public $queue = [];

    /** @var SerialDisposable */
    private $disposable;

    /**
     * ScheduledObserver constructor.
     * @param SchedulerInterface $scheduler
     * @param ObserverInterface $observer
     */
    public function __construct(SchedulerInterface $scheduler, ObserverInterface $observer)
    {
        $this->scheduler  = $scheduler;
        $this->observer   = $observer;
        $this->disposable = new SerialDisposable();
    }

    /**
     * @inheritdoc
     */
    protected function completed()
    {
        $this->queue[] = function () {
            $this->observer->onCompleted();
        };
    }

    /**
     * @inheritdoc
     */
    protected function next($value)
    {
        $this->queue[] = function () use ($value) {
            $this->observer->onNext($value);
        };
    }

    /**
     * @inheritdoc
     */
    protected function error(Exception $error)
    {
        $this->queue[] = function () use ($error) {
            $this->observer->onError($error);
        };
    }

    /**
     * @return void
     */
    public function ensureActive()
    {
        $isOwner = false;
        if (!$this->hasFaulted && count($this->queue) > 0) {
            $isOwner          = !$this->isAcquired;
            $this->isAcquired = true;
        }

        if (!$isOwner) {
            return;
        }

        $this->disposable->setDisposable(
            $this->scheduler->scheduleRecursive(
                function ($recurse) {
                    $parent = $this;
                    if (count($parent->queue) > 0) {
                        $work = array_shift($parent->queue);
                    } else {
                        $parent->isAcquired = false;

                        return;
                    }
                    try {
                        if (!is_callable($work)) {
                            throw new Exception("work is not callable");
                        }
                        $res = $work();
                    } catch (Exception $e) {
                        $res = $e;
                    }
                    if ($res instanceof Exception) {
                        $parent->queue      = [];
                        $parent->hasFaulted = true;
                        throw $res;
                    }
                    $recurse($parent);
                }
            )
        );
    }

    /**
     * @return void
     */
    public function dispose()
    {
        $this->disposable->dispose();
    }
}
