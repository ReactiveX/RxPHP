<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class IteratorObservable extends Observable
{
    public function __construct(
        private readonly \Iterator $items,
        private readonly null|SchedulerInterface $scheduler = null
    ) {
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $key = 0;

        $action = function ($reschedule) use (&$observer, &$key): void {
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
