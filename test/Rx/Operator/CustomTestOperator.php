<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class CustomTestOperator implements OperatorInterface
{
    private $mapTo;

    public function __construct($mapTo)
    {
        $this->mapTo = $mapTo;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(
        ObservableInterface $observable,
        ObserverInterface $observer,
        SchedulerInterface $scheduler = null
    ) {
        return $observable
            ->mapTo($this->mapTo)
            ->subscribe($observer, $scheduler);
    }
}
