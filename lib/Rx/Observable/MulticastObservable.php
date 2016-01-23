<?php

namespace Rx\Observable;

use Rx\Disposable\BinaryDisposable;
use Rx\Observable;
use Rx\ObserverInterface;

/**
 * Class MulticastObservable
 * @package Rx\Observable
 */
class MulticastObservable extends Observable
{
    /** @var \Rx\Observable */
    private $source;

    /** @var  callable */
    private $fn1;

    /** @var  callable */
    private $fn2;

    /**
     * MulticastObservable constructor.
     * @param $source
     * @param $fn1
     * @param $fn2
     */
    public function __construct(Observable $source, callable $fn1, callable $fn2)
    {
        $this->source = $source;
        $this->fn1    = $fn1;
        $this->fn2    = $fn2;
    }

    /**
     * @param \Rx\ObserverInterface $observer
     * @param null $scheduler
     * @return \Rx\Disposable\BinaryDisposable
     */
    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $connectable = $this->source->multicast(call_user_func($this->fn1));
        $observable  = call_user_func($this->fn2, $connectable);

        return new BinaryDisposable($observable->subscribe($observer, $scheduler), $connectable->connect());
    }
}
