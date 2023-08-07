<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\DisposableInterface;

/**
 * Class BinaryDisposable
 * @package Rx\Disposable
 */
class BinaryDisposable implements DisposableInterface
{
    /** @var ?\Rx\DisposableInterface */
    private $first;

    /** @var ?\Rx\DisposableInterface */
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

    public function dispose()
    {
        if ($this->isDisposed) {
            return;
        }

        $this->isDisposed = true;

        /** @phpstan-ignore-next-line */
        $this->first->dispose();
        /** @phpstan-ignore-next-line */
        $this->second->dispose();

        $this->first  = null;
        $this->second = null;
    }

    public function isDisposed(): bool
    {
        return $this->isDisposed;
    }
}
