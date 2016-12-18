<?php

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;

class IteratorObservable extends Observable
{
    private $items;

    private $scheduler;

    public function __construct(\Iterator $items, SchedulerInterface $scheduler = null)
    {
        $this->items     = $items;
        $this->scheduler = $scheduler ?: Scheduler::getDefault();
    }

    /**
     * @param ObserverInterface $observer
     * @return \Rx\Disposable\CompositeDisposable|\Rx\DisposableInterface
     */
    public function subscribe(ObserverInterface $observer): DisposableInterface
    {
        $key = 0;

        $defaultFn = function ($reschedule) use (&$observer, &$key) {
            try {
                if (null === $key) {
                    $observer->onCompleted();
                    return;
                }

                $current = $this->items->current();
                $observer->onNext($current);

                $this->items->next();
                $key = $this->items->key();

                $reschedule();

            } catch (\Exception $e) {
                $observer->onError($e);
            }
        };

        $hhvmFn = function ($reschedule) use (&$observer, &$key) {
            try {
                //HHVM requires you to call next() before current()
                $this->items->next();
                $key = $this->items->key();

                if (null === $key) {
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

        return $this->scheduler->scheduleRecursive(
            defined('HHVM_VERSION') && version_compare(HHVM_VERSION, '3.11.0', 'lt')
                ? $hhvmFn
                : $defaultFn
        );
    }
}
