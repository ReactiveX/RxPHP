<?php

namespace Rx\Observable;

use Rx\Disposable\CallbackDisposable;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Observer\AutoDetachObserver;
use Rx\Scheduler\ImmediateScheduler;

class AnonymousObservable extends Observable
{
    /** @var callable */
    private $subscribeAction;

    /**
     * @param callable $subscribeAction
     */
    public function __construct(callable $subscribeAction)
    {
        $this->subscribeAction = $subscribeAction;
    }

    /**
     * @inheritdoc
     */
    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        if (null === $scheduler) {
            $scheduler = new ImmediateScheduler();
        }

        $subscribeAction = $this->subscribeAction;

        $autoDetachObserver = new AutoDetachObserver($observer);

        $autoDetachObserver->setDisposable($subscribeAction($autoDetachObserver, $scheduler));

        return new CallbackDisposable(function () use ($autoDetachObserver) {
            $autoDetachObserver->dispose();
        });
    }
}
