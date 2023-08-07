<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\Disposable\BinaryDisposable;
use Rx\Disposable\CallbackDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

/**
 * @template T
 * @template-extends Observable<T>
 * Class ConnectableObservable
 * @package Rx\Observable
 */
class ConnectableObservable extends Observable
{
    /** @var \Rx\Subject\Subject<T> */
    protected $subject;

    /** @var  BinaryDisposable */
    protected $subscription;

    /** @var  Observable<T> */
    protected $sourceObservable;

    /** @var bool */
    protected $hasSubscription;

    /**
     * ConnectableObservable constructor.
     * @param Observable<T> $source
     * @param \Rx\Subject\Subject<T> $subject
     */
    public function __construct(Observable $source, Subject $subject = null)
    {
        $this->sourceObservable = $source->asObservable();
        $this->subject          = $subject ?: new Subject();
        $this->hasSubscription  = false;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        return $this->subject->subscribe($observer);
    }

    public function connect(): DisposableInterface
    {
        if ($this->hasSubscription) {
            return $this->subscription;
        }

        $this->hasSubscription = true;

        $connectableDisposable = new CallbackDisposable(function () {
            $this->hasSubscription = false;
        });

        $this->subscription = new BinaryDisposable($this->sourceObservable->subscribe($this->subject), $connectableDisposable);

        return $this->subscription;
    }

    /**
     * @return RefCountObservable<T>
     */
    public function refCount(): RefCountObservable
    {
        return new RefCountObservable($this);
    }
}
