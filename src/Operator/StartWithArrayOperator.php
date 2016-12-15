<?php

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

class StartWithArrayOperator implements OperatorInterface
{
    private $startArray;

    public function __construct(array $startArray)
    {
        $this->startArray = $startArray;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return Observable::fromArray($this->startArray)->concat($observable)->subscribe($observer);
    }
}
