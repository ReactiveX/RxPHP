<?php

declare(strict_types = 1);

namespace Rx\Scheduler;

use ReturnTypeWillChange;
use SplPriorityQueue;

/**
 * @internal
 *
 * @template TValue
 * @template TPriority
 * @template-extends SplPriorityQueue<TPriority, TValue>
 */
class InternalPriorityQueue extends SplPriorityQueue
{
    /**
     * use this value to "stabilize" the priority queue
     *
     * @var int
     */
    private $serial = PHP_INT_MAX;

    /**
     * @param ScheduledItem $item
     * @param int $priority
     * @return void
     * Look at this later
     * @phpstan-ignore-next-line
     */
    public function insert($item, $priority)
    {
        /**
         * Look at this later
         * @phpstan-ignore-next-line
         */
        parent::insert($item, [$priority, $this->serial--]);
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
