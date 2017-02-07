<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\DisposableInterface;

class RefCountDisposable implements DisposableInterface
{
    private $count = 0;
    private $disposable;
    private $isDisposed = false;
    private $isPrimaryDisposed = false;

    public function __construct(DisposableInterface $disposable)
    {
        $this->disposable = $disposable;
    }

    public function dispose()
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

    public function getDisposable()
    {
        if (!$this->isDisposed) {
            return $this->createInnerDisposable();
        }

        return new CallbackDisposable(function () {
        }); // no op
    }

    public function isDisposed()
    {
        return $this->isDisposed;
    }

    public function isPrimaryDisposed()
    {
        return $this->isPrimaryDisposed;
    }

    private function createInnerDisposable()
    {
        $count = &$this->count;
        $count++;
        $innerDisposable      = &$this;
        $isInnerDisposed      = false;
        $underLyingDisposable = &$this->disposable;

        return new CallbackDisposable(function () use (&$count, &$innerDisposable, &$isInnerDisposed) {
            if ($innerDisposable->isDisposed()) {
                return;
            }

            if ($isInnerDisposed) {
                return;
            }

            $isInnerDisposed = true;
            $count--;

            if ($count === 0 && $innerDisposable->isPrimaryDisposed()) {
                $innerDisposable->dispose();
            }
        });
    }
}
