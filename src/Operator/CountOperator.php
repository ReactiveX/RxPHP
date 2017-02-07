<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class CountOperator implements OperatorInterface
{
    private $count = 0;
    private $predicate;

    public function __construct(callable $predicate = null)
    {
        $this->predicate = $predicate;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $callbackObserver = new CallbackObserver(
            function ($x) use ($observer) {
                if ($this->predicate === null) {
                    $this->count++;

                    return;
                }
                try {
                    $predicate = $this->predicate;
                    if ($predicate($x)) {
                        $this->count++;
                    }
                } catch (\Throwable $e) {
                    $observer->onError($e);
                }
            },
            [$observer, 'onError'],
            function () use ($observer) {
                $observer->onNext($this->count);
                $observer->onCompleted();
            }
        );

        return $observable->subscribe($callbackObserver);
    }
}
