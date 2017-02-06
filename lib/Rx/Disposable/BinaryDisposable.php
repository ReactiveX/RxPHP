<?php

namespace Rx\Disposable;

use Rx\DisposableInterface;

/**
 * Class BinaryDisposable
 * @package Rx\Disposable
 */
class BinaryDisposable implements DisposableInterface
{
    /** @var DisposableInterface */
    private $first;

    /** @var DisposableInterface */
    private $second;

    /** @var bool */
    protected $isDisposed = false;

    /**
     * BinaryDisposable constructor.
     * @param DisposableInterface $first
     * @param DisposableInterface $second
     */
    public function __construct(DisposableInterface $first, DisposableInterface $second)
    {
        $this->first  = $first;
        $this->second = $second;
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

        $old1        = $this->first;
        $this->first = null;
        if ($old1) {
            $old1->dispose();
        }

        $old2         = $this->second;
        $this->second = null;
        if ($old2) {
            $old2->dispose();
        }

    }

    /**
     * @return bool
     */
    public function isDisposed()
    {
        return $this->isDisposed;
    }
}
