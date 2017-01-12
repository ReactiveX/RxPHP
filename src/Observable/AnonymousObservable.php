<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\Disposable\CallbackDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Observer\AutoDetachObserver;

class AnonymousObservable extends Observable
{
    private $subscribeAction;

    public function __construct(callable $subscribeAction)
    {
        $this->subscribeAction = $subscribeAction;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $subscribeAction = $this->subscribeAction;

        $autoDetachObserver = new AutoDetachObserver($observer);

        $autoDetachObserver->setDisposable($subscribeAction($autoDetachObserver));

        return new CallbackDisposable(function () use ($autoDetachObserver) {
            $autoDetachObserver->dispose();
        });
    }
}
