<?php

namespace Rx\Disposable;

use Rx\DisposableInterface;

class CompositeDisposable implements DisposableInterface
{
    private $disposables;

    public function __construct(array $disposables = array())
    {
        $this->disposables = $disposables;
    }

    public function dispose()
    {
        foreach ($this->disposables as $disposable) {
            $disposable->dispose();
        }

        $this->disposables = array();
    }

    public function add(DisposableInterface $disposable)
    {
        $this->disposables[] = $disposable;
    }

    public function remove(DisposableInterface $disposable)
    {
        foreach ($this->disposables as $i => $otherDisposable) {
            if ($otherDisposable === $disposable) {
                unset($this->disposables[$i]);
            }
        }
    }

    public function count()
    {
        return count($this->disposables);
    }
}
