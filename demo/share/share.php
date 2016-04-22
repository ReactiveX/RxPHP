<?php

require_once __DIR__ . '/../bootstrap.php';

$loop = \React\EventLoop\Factory::create();
$scheduler  = new \Rx\Scheduler\EventLoopScheduler($loop);

//With Share
$source = \Rx\Observable::interval(1000, $scheduler)
    ->take(2)
    ->doOnNext(function ($x) {
        echo "Side effect\n";
    });

$published = $source->share();

$published->subscribe($createStdoutObserver('SourceA '));
$published->subscribe($createStdoutObserver('SourceB '));

$loop->run();

