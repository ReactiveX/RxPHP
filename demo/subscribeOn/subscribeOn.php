<?php

require_once __DIR__ . '/../bootstrap.php';

use Interop\Async\Loop;
use Rx\Disposable\CallbackDisposable;
use Rx\ObserverInterface;
use Rx\Scheduler\EventLoopScheduler;

$observable = Rx\Observable::create(function (ObserverInterface $observer) {
    $handler = function () use ($observer) {
        $observer->onNext(42);
        $observer->onCompleted();
    };

    // Change scheduler for here
    $timer = Loop::delay(1, $handler);

    return new CallbackDisposable(function () use ($timer) {
        // And change scheduler for here
        if ($timer) {
            Loop::cancel($timer);
        }
    });
});

$observable
    ->subscribeOn(new EventLoopScheduler())
    ->subscribe($stdoutObserver);


//Next value: 42
//Complete!
