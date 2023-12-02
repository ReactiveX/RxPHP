<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\AsyncSchedulerInterface;

/**
 * @template T
 * @template-extends Observable<T>
 */
class TimerObservable extends Observable
{
    /**
     * @var int
     */
    private $dueTime;

    /**
     * @var AsyncSchedulerInterface
     */
    private $scheduler;

    public function __construct(int $dueTime, AsyncSchedulerInterface $scheduler)
    {
        $this->dueTime   = $dueTime;
        $this->scheduler = $scheduler;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        return $this->scheduler->schedule(
            function () use ($observer) {
                $observer->onNext(0);
                $observer->onCompleted();
            },
            $this->dueTime
        );
    }
}
