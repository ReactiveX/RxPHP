<?php

namespace Rx\Observable;

use Rx\Disposable\BinaryDisposable;
use Rx\Disposable\CallbackDisposable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

/**
 * Class ConnectableObservable
 * @package Rx\Observable
 */
class ConnectableObservable extends BaseObservable
{
    /** @var \Rx\Subject\Subject */
    protected $subject;

    /** @var  BinaryDisposable */
    protected $subscription;

    /** @var  ObserverInterface */
    protected $sourceObservable;

    /** @var bool */
    public $hasSubscription;

    /**
     * ConnectableObservable constructor.
     * @param \Rx\ObservableInterface $source
     * @param \Rx\Subject\Subject $subject
     */
    public function __construct(ObservableInterface $source, Subject $subject = null)
    {
        $this->sourceObservable = $source->asObservable();
        $this->subject          = $subject ?: new Subject();
        $this->hasSubscription  = false;
    }

    /**
     * @param \Rx\ObserverInterface $observer
     * @param null $scheduler
     * @return \Rx\Disposable\CallbackDisposable|\Rx\Disposable\EmptyDisposable|\Rx\DisposableInterface|\Rx\Subject\InnerSubscriptionDisposable
     */
    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        return $this->subject->subscribe($observer, $scheduler);
    }

    /**
     * @return \Rx\Disposable\BinaryDisposable
     */
    public function connect()
    {

        if ($this->hasSubscription) {
            return $this->subscription;
        }

        $this->hasSubscription = true;

        $isDisposed = false;

        $connectableDisposable = new CallbackDisposable(function () use (&$isDisposed) {
            if ($isDisposed) {
                return;
            }
            $isDisposed            = true;
            $this->hasSubscription = false;
        });

        $this->subscription = new BinaryDisposable($this->sourceObservable->subscribe($this->subject), $connectableDisposable);

        return $this->subscription;
    }

    /**
     * @return \Rx\Observable\RefCountObservable
     */
    public function refCount()
    {
        return new RefCountObservable($this);
    }

    /**
     * @param $scheduler
     */
    protected function doStart($scheduler)
    {
    }
}