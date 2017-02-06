<?php

namespace Rx\Disposable;

use RuntimeException;
use Rx\DisposableInterface;

class SingleAssignmentDisposable implements DisposableInterface
{
    /** @var DisposableInterface|null */
    private $current;

    /** @var bool */
    private $isDisposed = false;

    /**
     * @inheritdoc
     */
    public function dispose()
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

    /**
     * @param DisposableInterface|null $disposable
     * @return void
     */
    public function setDisposable(DisposableInterface $disposable = null)
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

    /**
     * @return null|DisposableInterface
     */
    public function getDisposable()
    {
        return $this->current;
    }

    /**
     * @return bool
     */
    public function isDisposed()
    {
        return $this->isDisposed;
    }
}
