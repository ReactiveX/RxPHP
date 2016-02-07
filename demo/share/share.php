<?php

require_once __DIR__ . '/../bootstrap.php';

$loop = \React\EventLoop\Factory::create();
$scheduler  = new \Rx\Scheduler\EventLoopScheduler($loop);

//Without Share
$source = \Rx\Observable::interval(1000, $scheduler)
    ->take(2)
    ->doOnNext(function ($x) {
        echo "Side effect\n";
    });

$source->subscribe($createStdoutObserver('SourceA'));
$source->subscribe($createStdoutObserver('SourceB'));

$loop->run();

//Side effect
//SourceA Next value: 0
//Side effect
//SourceB Next value: 0
//Side effect
//SourceA Next value: 1
//SourceA Complete!
//SourceB Next value: 1
//SourceB Complete!


//With Share
$source = \Rx\Observable::interval(1000, $scheduler)
    ->take(2)
    ->doOnNext(function ($x) {
        echo "Side effect\n";
    });

$published = $source->share();

$published->subscribe($createStdoutObserver('SourceA'));
$published->subscribe($createStdoutObserver('SourceB'));


$loop->run();

//Side effect
//SourceA Next value: 0
//SourceB Next value: 0
//Side effect
//SourceA Next value: 1
//SourceB Next value: 1
//SourceA Complete!
//SourceB Complete!
