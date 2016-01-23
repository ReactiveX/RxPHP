<?php

require_once __DIR__ . '/../bootstrap.php';

use Rx\ObserverInterface;
use Rx\Scheduler\EventLoopScheduler;

$loop       = \React\EventLoop\Factory::create();
$observable = Rx\Observable::create(function (ObserverInterface $observer) use ($loop) {
    $handler = function () use ($observer) {
        $observer->onNext(42);
        $observer->onCompleted();
    };

    // Change scheduler for here
    $timer = $loop->addTimer(1, $handler);

    return new \Rx\Disposable\CallbackDisposable(function () use ($timer) {
        // And change scheduler for here
        if ($timer) {
            $timer->cancel();
        }
    });
});

$observable
    ->subscribeOn(new EventLoopScheduler($loop))
    ->subscribe($stdoutObserver);

$loop->run();

//Next value: 42
//Complete!
