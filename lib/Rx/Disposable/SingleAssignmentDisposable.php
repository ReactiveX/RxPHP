<?php

namespace Rx\Disposable;

use RuntimeException;
use Rx\DisposableInterface;

class SingleAssignmentDisposable implements DisposableInterface
{
    private $current;

    public function dispose()
    {
        if (! $this->current) {
            throw new RuntimeException('No disposable set to dispose.');
        }

        $this->current->dispose();
        $this->current = null;
    }

    public function setDisposable(DisposableInterface $disposable = null)
    {
        if ($this->current) {
            throw new RuntimeException('Disposable has already been assigned.');
        }

        $this->current = $disposable;
    }

    public function getDisposable()
    {
        return $this->current;
    }
}
