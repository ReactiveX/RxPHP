<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class MinOperator implements OperatorInterface
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
        $previousMin = null;
        $comparing   = false;

        return $observable->subscribe(new CallbackObserver(
            function ($x) use (&$comparing, &$previousMin, $observer) {
                if (!$comparing) {
                    $comparing   = true;
                    $previousMin = $x;

                    return;
                }

                try {
                    $result = ($this->comparer)($x, $previousMin);
                    if ($result < 0) {
                        $previousMin = $x;
                    }
                } catch (\Throwable $e) {
                    $observer->onError($e);
                }
            },
            [$observer, 'onError'],
            function () use (&$comparing, &$previousMin, $observer) {
                if ($comparing) {
                    $observer->onNext($previousMin);
                    $observer->onCompleted();
                    return;
                }

                $observer->onError(new \Exception('Could not get minimum value because observable was empty.'));
            }
        ));
    }
}
