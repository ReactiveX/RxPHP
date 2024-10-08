<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class TakeWhileOperator implements OperatorInterface
{
    private $predicate;
    private $inclusive;

    public function __construct(callable $predicate, bool $inclusive = false)
    {
        $this->predicate = $predicate;
        $this->inclusive = $inclusive;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $onNext = function ($value) use ($observer) {
            try {
                if (($this->predicate)($value)) {
                    $observer->onNext($value);
                } else {
                    if ($this->inclusive) {
                        $observer->onNext($value);
                    }
                    $observer->onCompleted();
                }
            } catch (\Throwable $e) {
                $observer->onError($e);
            }
        };

        $callbackObserver = new CallbackObserver(
            $onNext,
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        return $observable->subscribe($callbackObserver);
    }
}
