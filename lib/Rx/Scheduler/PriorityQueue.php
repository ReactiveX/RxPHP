<?php

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
    public function compare($a, $b)
    {
        return $b->compareTo($a);
    }
}
