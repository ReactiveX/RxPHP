<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class MaxOperator implements OperatorInterface
{
    private $comparer;

    public function __construct(callable $comparer = null)
    {
        if ($comparer === null) {
            $comparer = function ($x, $y) {
                return $x > $y ? 1 : ($x < $y ? -1 : 0);
            };
        }

        $this->comparer = $comparer;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $previousMax = null;
        $comparing   = false;

        return $observable->subscribe(new CallbackObserver(
            function ($x) use (&$comparing, &$previousMax, $observer) {
                if (!$comparing) {
                    $comparing   = true;
                    $previousMax = $x;

                    return;
                }

                try {
                    $result = ($this->comparer)($x, $previousMax);
                    if ($result > 0) {
                        $previousMax = $x;
                    }
                } catch (\Throwable $e) {
                    $observer->onError($e);
                }
            },
            [$observer, 'onError'],
            function () use (&$comparing, &$previousMax, $observer) {
                if ($comparing) {
                    $observer->onNext($previousMax);
                    $observer->onCompleted();
                    return;
                }

                $observer->onError(new \Exception('Could not get maximum value because observable was empty.'));
            }
        ));
    }
}
