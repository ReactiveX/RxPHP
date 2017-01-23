<?php

namespace Rx\Disposable;

use Rx\DisposableInterface;

class RefCountDisposable implements DisposableInterface
{
    /** @var int */
    private $count = 0;

    /** @var DisposableInterface */
    private $disposable;

    /** @var bool */
    private $isDisposed = false;

    /** @var bool */
    private $isPrimaryDisposed = false;

    /**
     * @param DisposableInterface $disposable
     */
    public function __construct(DisposableInterface $disposable)
    {
        $this->disposable = $disposable;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @return CallbackDisposable
     */
    public function getDisposable()
    {
        if (!$this->isDisposed) {
            return $this->createInnerDisposable();
        }

        return new CallbackDisposable(function () {
        }); // no op
    }

    /**
     * @return bool
     */
    public function isDisposed()
    {
        return $this->isDisposed;
    }

    /**
     * @return bool
     */
    public function isPrimaryDisposed()
    {
        return $this->isPrimaryDisposed;
    }

    /**
     * @return CallbackDisposable
     */
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
