<?php

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class CatchErrorOperator implements OperatorInterface
{

    /** @var callable */
    private $errorSelector;

    public function __construct(callable $errorSelector)
    {
        $this->errorSelector = $errorSelector;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $isDisposed = false;
        $disposable = new CompositeDisposable();

        $onError = function (\Exception $e) use (&$isDisposed, $observer, $observable, $scheduler, $disposable) {

            if ($isDisposed) {
                return;
            }

            try {
                /** @var Observable $result */
                $result = call_user_func($this->errorSelector, $e, $observable);

                $subscription = $result->subscribe($observer, $scheduler);

                $disposable->add($subscription);

            } catch (\Exception $e) {
                $observer->onError($e);
            }
        };

        $callbackObserver = new CallbackObserver(
            [$observer, "onNext"],
            $onError,
            [$observer, "onCompleted"]
        );

        $subscription = $observable->subscribe($callbackObserver, $scheduler);

        $disposable->add($subscription);

        $disposable->add(new CallbackDisposable(function () use (&$isDisposed) {
            $isDisposed = true;
        }));

        return $disposable;
    }
}
