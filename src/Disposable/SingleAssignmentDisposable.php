<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use RuntimeException;
use Rx\DisposableInterface;

class SingleAssignmentDisposable implements DisposableInterface
{
    private null|DisposableInterface $current = null;
    private bool $isDisposed = false;

    public function dispose(): void
    {
        $old = null;

        if (!$this->isDisposed) {
            $this->isDisposed = true;
            $old              = $this->current;
            $this->current    = null;
        }

        if ($old) {
            $old->dispose();
        }
    }

    public function setDisposable(DisposableInterface $disposable = null): void
    {
        if ($this->current) {
            throw new RuntimeException('Disposable has already been assigned.');
        }

        if (!$this->isDisposed) {
            $this->current = $disposable;
        }

        if ($this->isDisposed && $disposable) {
            $disposable->dispose();
        }
    }

    public function getDisposable(): null|DisposableInterface
    {
        return $this->current;
    }

    public function isDisposed(): bool
    {
        return $this->isDisposed;
    }
}
