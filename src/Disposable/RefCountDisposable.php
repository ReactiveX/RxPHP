<?php

declare(strict_types=1);

namespace Rx\Disposable;

use Rx\DisposableInterface;

class RefCountDisposable implements DisposableInterface
{

    public function __construct(
        private readonly DisposableInterface $disposable,
        private int                          $count = 0,
        private bool                         $isDisposed = false,
        private bool                         $isPrimaryDisposed = false
    ) {
    }

    public function dispose(): void
    {
        if ($this->isDisposed) {
            return;
        }

        $this->isPrimaryDisposed = true;

        if ($this->count === 0) {
            $this->isDisposed = true;
            $this->disposable->dispose();
        }
    }

    public function getDisposable(): DisposableInterface|CallbackDisposable
    {
        if (!$this->isDisposed) {
            return $this->createInnerDisposable();
        }

        return new CallbackDisposable(function (): void {
        }); // no op
    }

    public function isDisposed(): bool
    {
        return $this->isDisposed;
    }

    public function isPrimaryDisposed(): bool
    {
        return $this->isPrimaryDisposed;
    }

    private function createInnerDisposable(): DisposableInterface
    {
        $this->count++;
        $isInnerDisposed = false;

        return new CallbackDisposable(function () use (&$isInnerDisposed): void {
            if ($this->isDisposed()) {
                return;
            }

            if ($isInnerDisposed) {
                return;
            }

            $isInnerDisposed = true;
            $this->count--;

            if ($this->count === 0 && $this->isPrimaryDisposed()) {
                $this->dispose();
            }
        });
    }
}
