<?php

declare(strict_types = 1);

namespace Rx\Scheduler;

use Rx\TestCase;

class PriorityQueueTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_remove_a_scheduled_item()
    {
        $queue          = new PriorityQueue();
        $scheduledItem  = $this->createScheduledItem(1);
        $scheduledItem2 = $this->createScheduledItem(1);
        $scheduledItem3 = $this->createScheduledItem(1);

        $queue->enqueue($scheduledItem);
        $queue->enqueue($scheduledItem2);
        $queue->enqueue($scheduledItem3);

        $this->assertEquals(3, $queue->count());
        $queue->remove($scheduledItem);
        $this->assertEquals(2, $queue->count());

        $this->assertSame($scheduledItem2, $queue->dequeue());
        $this->assertSame($scheduledItem3, $queue->dequeue());
    }

    private function createScheduledItem($dueTime)
    {
        return new ScheduledItem(null, null, null, $dueTime, null);
    }

    /**
     * @test
     */
    public function it_orders_the_items()
    {
        $queue          = new PriorityQueue();
        $scheduledItem  = $this->createScheduledItem(3);
        $scheduledItem2 = $this->createScheduledItem(2);
        $scheduledItem3 = $this->createScheduledItem(1);

        $queue->enqueue($scheduledItem);
        $queue->enqueue($scheduledItem2);
        $queue->enqueue($scheduledItem3);

        $this->assertEquals($scheduledItem3, $queue->dequeue());
        $this->assertEquals($scheduledItem2, $queue->dequeue());
        $this->assertEquals($scheduledItem, $queue->dequeue());
    }

    /**
     * @test
     */
    public function peek_returns_the_top_item()
    {
        $queue          = new PriorityQueue();
        $scheduledItem  = $this->createScheduledItem(3);
        $scheduledItem2 = $this->createScheduledItem(2);
        $scheduledItem3 = $this->createScheduledItem(1);

        $queue->enqueue($scheduledItem);
        $queue->enqueue($scheduledItem2);
        $queue->enqueue($scheduledItem3);

        $this->assertSame($scheduledItem3, $queue->peek());
    }

    /**
     * @test
     */
    public function dequeue_removes_the_top_item_from_the_queue()
    {
        $queue          = new PriorityQueue();
        $scheduledItem  = $this->createScheduledItem(1);
        $scheduledItem2 = $this->createScheduledItem(1);
        $scheduledItem3 = $this->createScheduledItem(1);

        $queue->enqueue($scheduledItem);
        $queue->enqueue($scheduledItem2);
        $queue->enqueue($scheduledItem3);

        $this->assertEquals(3, $queue->count());
        $this->assertSame($scheduledItem, $queue->dequeue());
        $this->assertEquals(2, $queue->count());

        $this->assertSame($scheduledItem2, $queue->dequeue());
        $this->assertSame($scheduledItem3, $queue->dequeue());
    }

    /**
     * @test
     */
    public function first_scheduled_item_with_same_priority_comes_first()
    {
        $queue          = new PriorityQueue();
        $scheduledItem  = $this->createScheduledItem(1);
        $scheduledItem2 = $this->createScheduledItem(1);
        $scheduledItem3 = $this->createScheduledItem(1);

        $queue->enqueue($scheduledItem);
        $queue->enqueue($scheduledItem2);
        $queue->enqueue($scheduledItem3);

        $this->assertSame($scheduledItem, $queue->dequeue());
        $this->assertSame($scheduledItem2, $queue->dequeue());
        $this->assertSame($scheduledItem3, $queue->dequeue());
    }

    /**
     * @test
     */
    public function can_remove_scheduled_items_out_of_order()
    {
        $queue          = new PriorityQueue();
        $scheduledItem  = $this->createScheduledItem(1);
        $scheduledItem2 = $this->createScheduledItem(2);
        $scheduledItem3 = $this->createScheduledItem(3);

        $queue->enqueue($scheduledItem);
        $queue->enqueue($scheduledItem2);
        $queue->enqueue($scheduledItem3);

        $queue->remove($scheduledItem2);
        $this->assertSame($scheduledItem, $queue->dequeue());
        $this->assertSame($scheduledItem3, $queue->dequeue());
    }

    /**
     * @test
     */
    public function should_not_remove_nonexistent_item()
    {
        $queue = new PriorityQueue();
        $queue->remove(
            new ScheduledItem(
                $this->createMock(ScheduledItem::class),
                null,
                function () {
                },
                0
            )
        );
    }
}
