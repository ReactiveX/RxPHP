<?php

namespace Rx\Disposable;

use Rx\DisposableInterface;

class EmptyDisposable implements DisposableInterface
{
    /**
     * @inheritdoc
     */
    public function dispose()
    {
        // do nothing \o/
    }
}
