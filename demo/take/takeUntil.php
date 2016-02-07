<?php

require_once __DIR__ . '/../bootstrap.php';

$loop    = \React\EventLoop\Factory::create();
$timeout = \Rx\Observable::create(function (\Rx\ObserverInterface $o) use ($loop) {
    $loop->addTimer(6, function () use ($o) {
        $o->onNext(0);
    });

    return new \Rx\Disposable\EmptyDisposable();
});

$scheduler  = new \Rx\Scheduler\EventLoopScheduler($loop);
$source = \Rx\Observable::interval(1000, $scheduler)->takeUntil($timeout);

$subscription = $source->subscribe($stdoutObserver);

$loop->run();
