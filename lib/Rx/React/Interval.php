<?php

namespace Rx\React;

use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\Timer;
use Rx\Disposable\CallbackDisposable;
use Rx\Observable\AnonymousObservable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class Interval extends AnonymousObservable
{

    /**
     * Interval constructor.
     *
     * @todo This needs to be tied to the scheduler
     *
     * @param integer $interval
     * @param LoopInterface $loop
     */
    public function __construct($interval, LoopInterface $loop)
    {
        parent::__construct(
            function (ObserverInterface $observer, SchedulerInterface $scheduler = null) use ($interval, $loop) {
                $counter = 0;
                /** @var Timer $timer */
                $timer = $loop->addPeriodicTimer($interval / 1000, function () use ($observer, &$counter) {
                    $observer->onNext($counter);
                    $counter++;
                });
                return new CallbackDisposable(
                    function () use ($timer) {
                        $timer->cancel();
                    }
                );
            }
        );
    }
}
