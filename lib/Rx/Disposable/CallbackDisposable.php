<?php

namespace Rx\Disposable;

use InvalidArgumentException;
use Rx\DisposableInterface;

class CallbackDisposable implements DisposableInterface
{
    private $action;

    public function __construct($action)
    {
        if ( ! is_callable($action)) {
            throw new InvalidArgumentException();
        }

        $this->action = $action;
    }

    public function dispose()
    {
        $call = $this->action;
        $call();
    }
}
