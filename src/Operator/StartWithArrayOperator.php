<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

final class StartWithArrayOperator implements OperatorInterface
{
    private $startArray;
    private $scheduler;

    public function __construct(array $startArray, SchedulerInterface $scheduler)
    {
        $this->startArray = $startArray;
        $this->scheduler  = $scheduler;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return Observable::fromArray($this->startArray, $this->scheduler)->concat($observable)->subscribe($observer);
    }
}
