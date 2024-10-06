<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\DisposableInterface;

class EmptyDisposable implements DisposableInterface
{
    public function dispose(): void
    {
        // do nothing \o/
    }
}
