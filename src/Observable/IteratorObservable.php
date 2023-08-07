<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

/**
 * @template T
 * @template-extends Observable<T>
 */
class IteratorObservable extends Observable
{
    /**
     * @var \Iterator
     */
    private $items;

    /**
     * @var SchedulerInterface
     */
    private $scheduler;

    public function __construct(\Iterator $items, SchedulerInterface $scheduler)
    {
        $this->items     = $items;
        $this->scheduler = $scheduler;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $key = 0;

        $action = function ($reschedule) use (&$observer, &$key) {
            try {
                if (null === $key || !$this->items->valid()) {

                    if ($this->items instanceof \Generator && $this->items->getReturn()) {
                        $observer->onNext($this->items->getReturn());
                    }

                    $observer->onCompleted();
                    return;
                }

                $current = $this->items->current();
                $observer->onNext($current);

                $this->items->next();
                $key = $this->items->key();

                $reschedule();

            } catch (\Throwable $e) {
                $observer->onError($e);
            }
        };

        return $this->scheduler->scheduleRecursive($action);
    }
}
