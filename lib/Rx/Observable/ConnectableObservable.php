<?php

namespace Rx\Observable;

use Rx\Disposable\BinaryDisposable;
use Rx\Disposable\CallbackDisposable;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;
use Rx\Subject\Subject;

/**
 * Class ConnectableObservable
 * @package Rx\Observable
 */
class ConnectableObservable extends Observable
{
    /** @var \Rx\Subject\Subject */
    protected $subject;

    /** @var  BinaryDisposable */
    protected $subscription;

    /** @var  Observable */
    protected $sourceObservable;

    /** @var bool */
    protected $hasSubscription;

    /** @var  SchedulerInterface */
    protected $scheduler;

    /**
     * ConnectableObservable constructor.
     * @param Observable $source
     * @param \Rx\Subject\Subject $subject
     * @param SchedulerInterface $scheduler
     */
    public function __construct(Observable $source, Subject $subject = null, SchedulerInterface $scheduler = null)
    {
        $this->sourceObservable = $source->asObservable();
        $this->subject          = $subject ?: new Subject();
        $this->hasSubscription  = false;
        $this->scheduler        = $scheduler;
    }

    /**
     * @param \Rx\ObserverInterface $observer
     * @param null $scheduler
     * @return CallbackDisposable|\Rx\Disposable\EmptyDisposable|\Rx\DisposableInterface|\Rx\Subject\InnerSubscriptionDisposable
     */
    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        if ($scheduler) {
            $this->scheduler = $scheduler;
        }

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

        $this->subscription = new BinaryDisposable($this->sourceObservable->subscribe($this->subject, $this->scheduler), $connectableDisposable);

        return $this->subscription;
    }

    /**
     * @return \Rx\Observable\RefCountObservable
     */
    public function refCount()
    {
        return new RefCountObservable($this);
    }
}
