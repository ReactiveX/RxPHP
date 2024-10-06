<?php

declare(strict_types = 1);

namespace Rx\Subject;

use Rx\DisposableInterface;
use Rx\ObserverInterface;

class InnerSubscriptionDisposable implements DisposableInterface
{
    public function __construct(
        private readonly Subject $subject,
        private null|ObserverInterface $observer
    ) {
    }

    public function dispose(): void
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
