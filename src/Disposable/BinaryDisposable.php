<?php

namespace Rx\Disposable;

use Rx\DisposableInterface;

/**
 * Class BinaryDisposable
 * @package Rx\Disposable
 */
class BinaryDisposable implements DisposableInterface
{
    /** @var \Rx\DisposableInterface */
    private $first;

    /** @var \Rx\DisposableInterface */
    private $second;

    /** @var bool */
    protected $isDisposed = false;

    /**
     * BinaryDisposable constructor.
     * @param $first
     * @param $second
     */
    public function __construct(DisposableInterface $first, DisposableInterface $second)
    {
        $this->first  = $first;
        $this->second = $second;
    }

    /**
     *
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
