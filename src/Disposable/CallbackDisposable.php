<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\DisposableInterface;

class CallbackDisposable implements DisposableInterface
{
    private $action;
    private $disposed = false;

    public function __construct(callable $action)
    {
        $this->action = $action;
    }

    public function dispose()
    {
        if ($this->disposed) {
            return;
        }
        $this->disposed = true;
        $call = $this->action;
        $call();
    }
}
