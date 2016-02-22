<?php

namespace Rx\Operator;

use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class StartWithArrayOperator implements OperatorInterface
{
    /** @var array  */
    private $startArray;

    public function __construct(array $startArray)
    {
        $this->startArray = $startArray;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        return Observable::fromArray($this->startArray)->concat($observable)->subscribe($observer, $scheduler);
    }
}
