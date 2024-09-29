<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\DisposableInterface;

class SerialDisposable implements DisposableInterface
{
    private bool $isDisposed = false;

    private ?DisposableInterface $disposable = null;

    public function dispose(): void
    {
        if ($this->isDisposed) {
            return;
        }

        $this->isDisposed = true;
        $old              = $this->disposable;
        $this->disposable = null;

        if ($old) {
            $old->dispose();
        }
    }

    public function getDisposable(): null|DisposableInterface
    {
        return $this->disposable;
    }

    public function setDisposable(DisposableInterface $disposable): void
    {
        $shouldDispose = $this->isDisposed;

        if (!$shouldDispose) {

            $old              = $this->disposable;
            $this->disposable = $disposable;

            if ($old) {
                $old->dispose();
            }
        }

        if ($shouldDispose) {
            $disposable->dispose();
        }
    }
}
