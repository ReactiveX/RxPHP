<?php

namespace Rx\Observable;

use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler\ImmediateScheduler;
use Rx\SchedulerInterface;

class IteratorObservable extends Observable
{
    /** @var \Iterator */
    private $items;

    public function __construct(\Iterator $items)
    {
        $this->items = $items;
    }

    /**
     * @param ObserverInterface $observer
     * @param SchedulerInterface|null $scheduler
     * @return \Rx\Disposable\CompositeDisposable|\Rx\DisposableInterface
     */
    public function subscribe(ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $scheduler = $scheduler ?: new ImmediateScheduler();

        return $scheduler->scheduleRecursive(function ($reschedule) use (&$observer) {
            try {

                if (null === $this->items->key()) {
                    $observer->onCompleted();
                    return;
                }

                $current = $this->items->current();
                $observer->onNext($current);
                $this->items->next();

                $reschedule();

            } catch (\Exception $e) {
                $observer->onError($e);
            }
        });
    }
}
