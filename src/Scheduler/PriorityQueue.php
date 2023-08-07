<?php

declare(strict_types = 1);

namespace Rx\Scheduler;

use ReturnTypeWillChange;
use SplPriorityQueue;

class PriorityQueue
{
    /**
     * @var InternalPriorityQueue<int, ScheduledItem>
     */
    private $queue;

    public function __construct()
    {
        $this->queue = new InternalPriorityQueue;
    }

    /**
     * @return void
     */
    public function enqueue(ScheduledItem $item)
    {
        /** @phpstan-ignore-next-line */
        $this->queue->insert($item, $item);
    }

    /**
     * @return bool
     */
    public function remove(ScheduledItem $item)
    {
        if ($this->count() === 0) {
            return false;
        }

        if ($this->peek() === $item) {
            $this->dequeue();
            return true;
        }

        /**
         * @var InternalPriorityQueue<int, ScheduledItem> $newQueue
         */
        $newQueue = new InternalPriorityQueue();
        $removed  = false;

        foreach ($this->queue as $element) {
            /**
             * Look at this later
             * @phpstan-ignore-next-line
             */
            if ($item === $element) {
                $removed = true;
                continue;
            }

            /**
             * Look at this later
             * @phpstan-ignore-next-line
             */
            $newQueue->insert($element, $element);
        }

        $this->queue = $newQueue;

        return $removed;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->queue->count();
    }

    /**
     * @return ScheduledItem
     */
    public function peek()
    {
        $return = $this->queue->top();
        assert($return instanceof ScheduledItem);
        return $return;
    }

    /**
     * @return ScheduledItem
     */
    public function dequeue()
    {
        $return = $this->queue->extract();
        assert($return instanceof ScheduledItem);
        return $return;
    }
}
