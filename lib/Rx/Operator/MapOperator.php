<?php

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class MapOperator implements OperatorInterface
{
    /** @var callable */
    private $selector;

    /**
     * MapOperator constructor.
     * @param $selector
     */
    public function __construct(callable $selector)
    {
        $this->selector = $selector;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $disposed   = false;
        $disposable = new CompositeDisposable();

        $selectObserver = new CallbackObserver(
            function ($nextValue) use ($observer, &$disposed) {

                $value = null;
                try {
                    $value = call_user_func_array($this->selector, [$nextValue]);
                } catch (\Exception $e) {
                    $observer->onError($e);
                }
                if (!$disposed) {
                    $observer->onNext($value);
                }
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        $disposable->add(new CallbackDisposable(function () use (&$disposed) {
            $disposed = true;
        }));

        $disposable->add($observable->subscribe($selectObserver, $scheduler));

        return $disposable;
    }
}
