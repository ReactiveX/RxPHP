<?php

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

class MapOperator implements OperatorInterface
{
    private $selector;

    public function __construct(callable $selector)
    {
        $this->selector = $selector;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
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

        $disposable->add($observable->subscribe($selectObserver));

        return $disposable;
    }
}
