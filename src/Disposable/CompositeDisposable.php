<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\DisposableInterface;

class CompositeDisposable implements DisposableInterface
{

    public function __construct(
        private array $disposables = [],
        private bool $isDisposed = false
    ) {
    }

    public function dispose(): void
    {
        if ($this->isDisposed) {
            return;
        }

        $this->isDisposed = true;

        $disposables       = $this->disposables;
        $this->disposables = [];

        foreach ($disposables as $disposable) {
            $disposable->dispose();
        }
    }

    public function add(DisposableInterface $disposable): void
    {
        if ($this->isDisposed) {
            $disposable->dispose();
        } else {
            $this->disposables[] = $disposable;
        }
    }

    public function remove(DisposableInterface $disposable): bool
    {
        if ($this->isDisposed) {
            return false;
        }

        $key = array_search($disposable, $this->disposables, true);

        if (false === $key) {
            return false;
        }

        $removedDisposable = $this->disposables[$key];
        unset($this->disposables[$key]);
        $removedDisposable->dispose();

        return true;
    }

    public function contains(DisposableInterface $disposable): bool
    {
        return in_array($disposable, $this->disposables, true);
    }

    public function count(): int
    {
        return count($this->disposables);
    }

    public function clear(): void
    {
        $disposables       = $this->disposables;
        $this->disposables = [];

        foreach ($disposables as $disposable) {
            $disposable->dispose();
        }
    }
}
