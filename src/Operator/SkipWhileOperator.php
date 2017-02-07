<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class SkipWhileOperator implements OperatorInterface
{
    /** @var callable */
    private $predicate;

    /** @var bool */
    private $isSkipping;

    public function __construct(callable $predicate)
    {
        $this->predicate  = $predicate;
        $this->isSkipping = true;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $callbackObserver = new CallbackObserver(
            function ($value) use ($observer, $observable) {
                try {

                    if ($this->isSkipping) {
                        $this->isSkipping = ($this->predicate)($value, $observable);
                    }

                    if (!$this->isSkipping) {
                        $observer->onNext($value);
                    }

                } catch (\Throwable $e) {
                    $observer->onError($e);
                }
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        return $observable->subscribe($callbackObserver);
    }
}
