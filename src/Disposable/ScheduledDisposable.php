<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\DisposableInterface;
use Rx\SchedulerInterface;

class ScheduledDisposable implements DisposableInterface
{
    /** @var DisposableInterface */
    private $disposable;

    /** @var SchedulerInterface */
    private $scheduler;

    /** @var bool */
    protected $isDisposed = false;

    public function __construct(SchedulerInterface $scheduler, DisposableInterface $disposable)
    {
        $this->scheduler  = $scheduler;
        $this->disposable = $disposable;
    }

    public function dispose()
    {
        if ($this->isDisposed) {
            return;
        }

        $this->isDisposed = true;

        $this->scheduler->schedule(function () {
            $this->disposable->dispose();
        });
    }
}
