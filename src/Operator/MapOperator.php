<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class MapOperator implements OperatorInterface
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
                    $value = ($this->selector)($nextValue);
                } catch (\Throwable $e) {
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
