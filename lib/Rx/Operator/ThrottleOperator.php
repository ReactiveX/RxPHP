<?php

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class ThrottleOperator implements OperatorInterface
{
    private $nextSend = 0;
    
    private $throttleTime = 0;
    
    private $completed = false;
    
    /** @var SchedulerInterface */
    private $scheduler;

    /**
     * DebounceOperator constructor.
     * @param int $debounceTime
     * @param SchedulerInterface $scheduler
     */
    public function __construct($debounceTime, SchedulerInterface $scheduler = null)
    {
        $this->throttleTime = $debounceTime;
        $this->scheduler    = $scheduler;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(
        ObservableInterface $observable,
        ObserverInterface $observer,
        SchedulerInterface $scheduler = null
    ) {
        if ($this->scheduler !== null) {
            $scheduler = $this->scheduler;
        }

        $innerDisp = new SerialDisposable();
        
        $disp = $observable->subscribe(new CallbackObserver(
            function ($x) use ($innerDisp, $observer, $scheduler) {
                $now = $scheduler->now();
                if ($this->nextSend <= $now) {
                    $innerDisp->setDisposable(new EmptyDisposable());
                    $observer->onNext($x);
                    $this->nextSend = $now + $this->throttleTime - 1;
                    return;
                }
                
                $newDisp = Observable::just($x)
                    ->delay($this->nextSend - $now)
                    ->subscribe(new CallbackObserver(
                        function ($x) use ($observer, $scheduler) {
                            $observer->onNext($x);
                            $this->nextSend = $scheduler->now() + $this->throttleTime - 1;
                            if ($this->completed) {
                                $observer->onCompleted();
                            }
                        },
                        [$observer, 'onError']
                    ), $scheduler);
                
                $innerDisp->setDisposable($newDisp);
            },
            function (\Exception $e) use ($observer, $innerDisp) {
                $innerDisp->dispose();
                $observer->onError($e);
            },
            function () use ($observer) {
                $this->completed = true;
                if ($this->nextSend === 0) {
                    $observer->onCompleted();
                }
            }
        ), $scheduler);

        return new CompositeDisposable([$disp, $innerDisp]);
    }
}
