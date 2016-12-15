<?php

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;

class ThrottleOperator implements OperatorInterface
{
    private $nextSend = 0;

    private $throttleTime = 0;

    private $completed = false;

    private $scheduler;

    public function __construct(int $debounceTime, SchedulerInterface $scheduler = null)
    {
        $this->throttleTime = $debounceTime;
        $this->scheduler    = $scheduler;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $scheduler = $this->scheduler ?? Scheduler::getDefault();

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
                    ));

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
        ));

        return new CompositeDisposable([$disp, $innerDisp]);
    }
}
