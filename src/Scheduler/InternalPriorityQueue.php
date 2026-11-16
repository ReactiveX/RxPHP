<?php

declare(strict_types = 1);

namespace Rx\Scheduler;

use ReturnTypeWillChange;
use SplPriorityQueue;

/**
 * @internal
 *
 * @template-extends SplPriorityQueue<array{0: ScheduledItem, 1: int}, ScheduledItem>
 */
class InternalPriorityQueue extends SplPriorityQueue
{
    private $serial = PHP_INT_MAX;

    /**
     * @param ScheduledItem $item
     * @param ScheduledItem $priority
     * @return true
     *
     * @phpstan-ignore method.childParameterType
     */
    #[ReturnTypeWillChange]
    public function insert($item, $priority)
    {
        parent::insert($item, [$priority, $this->serial--]);

        return true;
    }

    /**
     * @param array{0: ScheduledItem, 1: int} $a
     * @param array{0: ScheduledItem, 1: int} $b
     * @return int
     */
    #[ReturnTypeWillChange]
    public function compare($a, $b)
    {
        $value = $b[0]->compareTo($a[0]);

        if (0 === $value) {
            return $a[1] < $b[1] ? -1 : 1;
        }

        return $value;
    }
}
