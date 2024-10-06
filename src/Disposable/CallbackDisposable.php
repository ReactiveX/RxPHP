<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\DisposableInterface;

class CallbackDisposable implements DisposableInterface
{
    public function __construct(
        private \Closure $action,
        private bool $disposed = false
    ) {
    }

    public function dispose(): void
    {
        if ($this->disposed) {
            return;
        }
        $this->disposed = true;
        $call = $this->action;
        $call();
    }
}
