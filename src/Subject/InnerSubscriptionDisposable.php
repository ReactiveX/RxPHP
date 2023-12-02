<?php

declare(strict_types = 1);

namespace Rx\Subject;

use Rx\DisposableInterface;
use Rx\ObserverInterface;

/**
 * @template T
 */
class InnerSubscriptionDisposable implements DisposableInterface
{
    /**
     * @var ?ObserverInterface
     */
    private $observer;

    /**
     * @var Subject<T>
     */
    private $subject;

    /**
     * @param Subject<T> $subject
     * @param ObserverInterface $observer
     */
    public function __construct(Subject $subject, ObserverInterface $observer)
    {
        $this->subject  = $subject;
        $this->observer = $observer;
    }

    /**
     * @return void
     */
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
