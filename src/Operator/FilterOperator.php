<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class FilterOperator implements OperatorInterface
{
    private $predicate;

    public function __construct(callable $predicate)
    {
        $this->predicate = $predicate;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $selectObserver = new CallbackObserver(
            function ($nextValue) use ($observer) {
                $shouldFire = false;
                try {
                    $shouldFire = ($this->predicate)($nextValue);
                } catch (\Throwable $e) {
                    $observer->onError($e);
                }

                if ($shouldFire) {
                    $observer->onNext($nextValue);
                }
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        return $observable->subscribe($selectObserver);
    }
}
