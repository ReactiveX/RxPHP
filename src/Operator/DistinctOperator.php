<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

final class DistinctOperator implements OperatorInterface
{
    public function __construct(
        private readonly null|\Closure $keySelector = null,
        private readonly null|\Closure $comparer = null
    ) {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $values = [];

        $callbackObserver = new CallbackObserver(
            function ($value) use ($observer, &$values) {

                try {
                    $key = $this->keySelector ? ($this->keySelector)($value) : $value;

                    if ($this->comparer) {
                        foreach ($values as $v) {
                            $comparerEquals = call_user_func($this->comparer, $key, $v);

                            if ($comparerEquals) {
                                return;
                            }
                        }
                        $values[] = $key;
                    } else {
                        if (array_key_exists($key, $values)) {
                            return;
                        }
                        $values[$key] = null;
                    }

                    $observer->onNext($value);

                } catch (\Throwable $e) {
                    return $observer->onError($e);
                }
            },
            fn ($err) => $observer->onError($err),
            fn () => $observer->onCompleted()
        );

        return $observable->subscribe($callbackObserver);
    }
}
