<?php

namespace Rx\Custom\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\Operator\OperatorInterface;

class CustomTestOperator implements OperatorInterface
{
    private $mapTo;

    public function __construct($mapTo)
    {
        $this->mapTo = $mapTo;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return $observable
            ->mapTo($this->mapTo)
            ->subscribe($observer);
    }
}
