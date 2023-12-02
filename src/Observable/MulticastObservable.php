<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\Disposable\BinaryDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;

/**
 * @template T
 * @template-extends Observable<T>
 * Class MulticastObservable
 * @package Rx\Observable
 */
class MulticastObservable extends Observable
{
    /** @var \Rx\Observable<T> */
    private $source;

    /** @var callable */
    private $fn1;

    /** @var callable */
    private $fn2;

    /**
     * MulticastObservable constructor.
     * @param Observable<T> $source
     * @param callable $fn1
     * @param callable $fn2
     */
    public function __construct(Observable $source, callable $fn1, callable $fn2)
    {
        $this->source = $source;
        $this->fn1    = $fn1;
        $this->fn2    = $fn2;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $connectable = $this->source->multicast(($this->fn1)());
        $observable  = ($this->fn2)($connectable);

        return new BinaryDisposable($observable->subscribe($observer), $connectable->connect());
    }
}
