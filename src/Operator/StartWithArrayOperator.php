<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

/**
 * @template T
 */
final class StartWithArrayOperator implements OperatorInterface
{
    /**
     * @var array<T>
     */
    private $startArray;

    /**
     * @var SchedulerInterface
     */
    private $scheduler;

    /**
     * @param array<T> $startArray
     */
    public function __construct(array $startArray, SchedulerInterface $scheduler)
    {
        $this->startArray = $startArray;
        $this->scheduler  = $scheduler;
    }

    /**
     * @param ObservableInterface<T> $observable
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return Observable::fromArray($this->startArray, $this->scheduler)->concat($observable)->subscribe($observer);
    }
}
