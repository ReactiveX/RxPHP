<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

final readonly class StartWithArrayOperator implements OperatorInterface
{
    public function __construct(
        private array              $startArray,
        private SchedulerInterface $scheduler
    ) {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return Observable::fromArray($this->startArray, $this->scheduler)->concat($observable)->subscribe($observer);
    }
}
