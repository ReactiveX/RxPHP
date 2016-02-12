<?php

namespace Rx\Observable;

use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;
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
        $scheduler = $scheduler ?: Scheduler::getDefault();
        $key       = 0;

        return $scheduler->scheduleRecursive(function ($reschedule) use (&$observer, &$key) {
            try {

                //HHVM requires you to call next() before current()
                if (defined('HHVM_VERSION')) {
                    $this->items->next();
                    $key = $this->items->key();
                }
                if (null === $key) {
                    $observer->onCompleted();
                    return;
                }

                $current = $this->items->current();
                $observer->onNext($current);

                if (!defined('HHVM_VERSION')) {
                    $this->items->next();
                    $key = $this->items->key();
                }

                $reschedule();

            } catch (\Exception $e) {
                $observer->onError($e);
            }
        });
    }
}
