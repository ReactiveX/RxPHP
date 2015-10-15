<?php


namespace Rx\Operator;

use Rx\Disposable\EmptyDisposable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;


class ToArrayOperator implements OperatorInterface
{
    /** @var array  */
    private $arr = [];


    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        return $observable->subscribe(new CallbackObserver(
            function ($x) {
                $this->arr[] = $x;
            },
            function ($e) use ($observer) {
                $observer->onError($e);
            },
            function() use ($observer) {
                $observer->onNext($this->arr);
                $observer->onCompleted();
            }
        ));
    }
}