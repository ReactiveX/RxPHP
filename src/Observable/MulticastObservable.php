<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\Disposable\BinaryDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;

class MulticastObservable extends Observable
{
    public function __construct(
        private readonly Observable $source,
        private readonly \Closure   $fn1,
        private readonly \Closure $fn2
    ) {
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $connectable = $this->source->multicast(($this->fn1)());
        $observable  = ($this->fn2)($connectable);

        return new BinaryDisposable($observable->subscribe($observer), $connectable->connect());
    }
}
