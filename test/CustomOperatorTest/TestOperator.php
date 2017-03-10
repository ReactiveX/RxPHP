<?php

declare(strict_types = 1);

namespace CustomOperatorTest\Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\Operator\OperatorInterface;

class TestOperator implements OperatorInterface
{
    private $mapTo;

    public function __construct($mapTo)
    {
        $this->mapTo = $mapTo;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return $observable
            ->mapTo($this->mapTo)
            ->subscribe($observer);
    }
}
