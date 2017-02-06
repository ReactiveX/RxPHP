<?php

namespace Rx\Disposable;

use Rx\DisposableInterface;

class CompositeDisposable implements DisposableInterface
{
    /** @var DisposableInterface[]  */
    private $disposables;

    /** @var bool */
    private $isDisposed = false;

    /**
     * @param DisposableInterface[] $disposables
     */
    public function __construct(array $disposables = [])
    {
        $this->disposables = $disposables;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @param DisposableInterface $disposable
     * @return void
     */
    public function add(DisposableInterface $disposable)
    {
        if ($this->isDisposed) {
            $disposable->dispose();
        } else {
            $this->disposables[] = $disposable;
        }
    }

    /**
     * @param DisposableInterface $disposable
     * @return bool
     */
    public function remove(DisposableInterface $disposable)
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

    /**
     * @param DisposableInterface $disposable
     * @return bool
     */
    public function contains(DisposableInterface $disposable)
    {
        return in_array($disposable, $this->disposables, true);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->disposables);
    }

    /**
     * @return void
     */
    public function clear()
    {
        $disposables       = $this->disposables;
        $this->disposables = [];

        foreach ($disposables as $disposable) {
            $disposable->dispose();
        }
    }
}
