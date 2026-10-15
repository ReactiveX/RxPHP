<?php

declare(strict_types = 1);

namespace Rx\Subject;

use Rx\DisposableInterface;
use Rx\ObserverInterface;

/** @template T */
class InnerSubscriptionDisposable implements DisposableInterface
{
    private $observer;
    /** @var Subject<T> */
    private $subject;

    /** @param Subject<T> $subject */
    public function __construct(Subject $subject, ObserverInterface $observer)
    {
        $this->subject  = $subject;
        $this->observer = $observer;
    }

    public function dispose()
    {
        if ($this->subject->isDisposed()) {
            return;
        }

        if (null === $this->observer) {
            return;
        }

        $this->subject->removeObserver($this->observer);
        $this->observer = null;
    }
}
