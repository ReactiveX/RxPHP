<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class TakeLastOperator implements OperatorInterface
{
    private array $items = [];

    public function __construct(private readonly int $count)
    {
        if ($count < 0) {
            throw new \InvalidArgumentException('Count must be >= 0');
        }
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $callbackObserver = new CallbackObserver(
            function ($nextValue) use ($observer): void {
                $this->items[] = $nextValue;

                if (count($this->items) > $this->count) {
                    array_shift($this->items);
                }
            },
            fn ($err) => $observer->onError($err),
            function () use ($observer): void {

                while (count($this->items) > 0) {
                    $observer->onNext(array_shift($this->items));
                }

                $observer->onCompleted();
            }
        );

        return $observable->subscribe($callbackObserver);
    }
}
