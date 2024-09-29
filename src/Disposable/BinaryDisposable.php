<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\DisposableInterface;

class BinaryDisposable implements DisposableInterface
{
    public function __construct(
        private null|DisposableInterface $first,
        private null|DisposableInterface $second,
        protected bool $isDisposed = false
    ) {
    }

    public function dispose(): void
    {
        if ($this->isDisposed) {
            return;
        }

        $this->isDisposed = true;

        $this->first->dispose();
        $this->second->dispose();

        $this->first  = null;
        $this->second = null;
    }

    public function isDisposed(): bool
    {
        return $this->isDisposed;
    }
}
