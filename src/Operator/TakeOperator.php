<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class TakeOperator implements OperatorInterface
{
    private $count;

    public function __construct(int $count)
    {
        if ($count < 0) {
            throw new \InvalidArgumentException('Count must be >= 0');
        }

        $this->count = $count;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $remaining = $this->count;

        $callbackObserver = new CallbackObserver(
            function ($nextValue) use ($observer, &$remaining) {
                if ($remaining > 0) {
                    $remaining--;
                    $observer->onNext($nextValue);
                    if ($remaining === 0) {
                        $observer->onCompleted();
                    }
                }
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        return $observable->subscribe($callbackObserver);
    }
}
