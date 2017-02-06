<?php

namespace Rx\Disposable;

use Rx\DisposableInterface;

class CallbackDisposable implements DisposableInterface
{
    /** @var callable */
    private $action;

    /**
     * @param callable $action
     */
    public function __construct(callable $action)
    {
        $this->action = $action;
    }

    /**
     * @inheritdoc
     */
    public function dispose()
    {
        $call = $this->action;
        $call();
    }
}
