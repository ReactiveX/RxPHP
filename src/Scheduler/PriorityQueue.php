<?php

declare(strict_types = 1);

namespace Rx\Scheduler;

use SplPriorityQueue;

class PriorityQueue
{
    private $queue;

    public function __construct()
    {
        $this->queue = new InternalPriorityQueue;
    }

    public function enqueue(ScheduledItem $item)
    {
        $this->queue->insert($item, $item);
    }

    public function remove($item)
    {
        if ($this->count() === 0) {
            return false;
        }

        if ($this->peek() === $item) {
            $this->dequeue();
            return true;
        }
        $newQueue = new InternalPriorityQueue();
        $removed  = false;

        foreach ($this->queue as $element) {
            if ($item === $element) {
                $removed = true;
                continue;
            }

            $newQueue->insert($element, $element);
        }

        $this->queue = $newQueue;

        return $removed;
    }

    public function count()
    {
        return $this->queue->count();
    }

    public function peek()
    {
        return $this->queue->top();
    }

    public function dequeue()
    {
        return $this->queue->extract();
    }
}

/**
 * @internal
 */
class InternalPriorityQueue extends SplPriorityQueue
{
    // use this value to "stabilize" the priority queue
    private $serial = PHP_INT_MAX;

    public function insert($item, $priority)
    {
        parent::insert($item, [$priority, $this->serial--]);
    }

    public function compare($a, $b)
    {
        $value = $b[0]->compareTo($a[0]);

        if (0 === $value) {
            return $a[1] < $b[1] ? -1 : 1;
        }

        return $value;
    }
}
