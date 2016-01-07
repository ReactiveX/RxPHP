<?php

require_once __DIR__ . '/../bootstrap.php';

$loop = \React\EventLoop\Factory::create();

//Without Share
$interval = new \Rx\React\Interval(1000, $loop);

$source = $interval
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

$interval = new \Rx\React\Interval(1000, $loop);

$source = $interval
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
