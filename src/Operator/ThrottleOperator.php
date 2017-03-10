<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

final class ThrottleOperator implements OperatorInterface
{
    private $nextSend = 0;

    private $throttleTime = 0;

    private $completed = false;

    private $scheduler;

    public function __construct(int $debounceTime, SchedulerInterface $scheduler)
    {
        $this->throttleTime = $debounceTime;
        $this->scheduler    = $scheduler;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $innerDisp = new SerialDisposable();

        $disp = $observable->subscribe(new CallbackObserver(
            function ($x) use ($innerDisp, $observer) {
                $now = $this->scheduler->now();
                if ($this->nextSend <= $now) {
                    $innerDisp->setDisposable(new EmptyDisposable());
                    $observer->onNext($x);
                    $this->nextSend = $now + $this->throttleTime - 1;
                    return;
                }

                $newDisp = Observable::of($x, $this->scheduler)
                    ->delay($this->nextSend - $now, $this->scheduler)
                    ->subscribe(new CallbackObserver(
                        function ($x) use ($observer) {
                            $observer->onNext($x);
                            $this->nextSend = $this->scheduler->now() + $this->throttleTime - 1;
                            if ($this->completed) {
                                $observer->onCompleted();
                            }
                        },
                        [$observer, 'onError']
                    ));

                $innerDisp->setDisposable($newDisp);
            },
            function (\Throwable $e) use ($observer, $innerDisp) {
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
