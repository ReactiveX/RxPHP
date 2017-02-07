<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\DisposableInterface;

class CompositeDisposable implements DisposableInterface
{
    private $disposables;
    private $isDisposed = false;

    public function __construct(array $disposables = [])
    {
        $this->disposables = $disposables;
    }

    public function dispose()
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

    public function add(DisposableInterface $disposable)
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

    public function count()
    {
        return count($this->disposables);
    }

    public function clear()
    {
        $disposables       = $this->disposables;
        $this->disposables = [];

        foreach ($disposables as $disposable) {
            $disposable->dispose();
        }
    }
}
