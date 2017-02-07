<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class SkipLastOperator implements OperatorInterface
{
    private $count;

    /** @var array */
    private $q;

    public function __construct(int $count)
    {
        $this->count = $count;
        if ($this->count < 0) {
            throw new \InvalidArgumentException('Argument Out of Range');
        }
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $this->q    = [];
        $cbObserver = new CallbackObserver(
            function ($x) use ($observer) {
                $this->q[] = $x;
                if (count($this->q) > $this->count) {
                    $observer->onNext(array_shift($this->q));
                }
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        return $observable->subscribe($cbObserver);
    }
}
