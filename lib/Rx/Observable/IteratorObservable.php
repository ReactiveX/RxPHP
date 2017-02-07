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
    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $scheduler = $scheduler ?: new ImmediateScheduler();
        
        $defaultFn = function ($reschedule) use (&$observer) {
            try {
                if (!$this->items->valid()) {
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
        };
        
        $hhvmFn = function ($reschedule) use (&$observer) {
            try {
                //HHVM requires you to call next() before current()
                $this->items->next();

                if (!$this->items->valid()) {
                    $observer->onCompleted();
                    return;
                }

                $current = $this->items->current();
                $observer->onNext($current);

                $reschedule();
            } catch (\Exception $e) {
                $observer->onError($e);
            }
        };

        return $scheduler->scheduleRecursive(
            defined('HHVM_VERSION') && version_compare(HHVM_VERSION, '3.11.0', 'lt')
                ? $hhvmFn
                : $defaultFn
        );
    }
}
