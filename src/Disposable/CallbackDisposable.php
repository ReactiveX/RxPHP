<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\DisposableInterface;

class CallbackDisposable implements DisposableInterface
{
    private $action;

    public function __construct(callable $action)
    {
        $this->action = $action;
    }

    public function dispose()
    {
        $call = $this->action;
        $call();
    }
}
