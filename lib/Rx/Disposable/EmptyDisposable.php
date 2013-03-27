<?php

namespace Rx\Disposable;

use Rx\DisposableInterface;

class EmptyDisposable implements DisposableInterface
{
    public function dispose()
    {
        // do nothing \o/
    }
}
