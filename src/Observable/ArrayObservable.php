<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

/**
 * @template  T
 * @template-extends Observable<T>
 */
class ArrayObservable extends Observable
{
    /**
     * @var array<T>
     */
    private $data;

    /**
     * @var SchedulerInterface
     */
    private $scheduler;

    /**
     * @param array<T> $data
     */
    public function __construct(array $data, SchedulerInterface $scheduler)
    {
        $this->data      = $data;
        $this->scheduler = $scheduler;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $values = &$this->data;
        $max    = count($values);
        $keys   = array_keys($values);
        $count  = 0;

        return $this->scheduler->scheduleRecursive(function ($reschedule) use (&$observer, &$values, $max, &$count, $keys) {
            if ($count < $max) {
                $observer->onNext($values[$keys[$count]]);
                $count++;
                /**
                 * @phpstan-ignore-next-line
                 *
                 * Ignores the following error: Comparison operation ">=" between int<1, max> and 1 is always true.
                 */
                if ($count >= 1) {
                    $reschedule();
                    return;
                }
            }
            $observer->onCompleted();
        });
    }
}
