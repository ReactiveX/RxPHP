<?php

require_once __DIR__ . '/../bootstrap.php';

$loop    = \React\EventLoop\Factory::create();
$timeout = \Rx\Observable::create(function (\Rx\ObserverInterface $o) use ($loop) {
    $loop->addTimer(6, function () use ($o) {
        $o->onNext(0);
    });

    return new \Rx\Disposable\EmptyDisposable();
});

$source = (new \Rx\React\Interval(1000, $loop))->takeUntil($timeout);

$subscription = $source->subscribe($stdoutObserver);

$loop->run();
