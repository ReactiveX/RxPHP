<?php

require_once __DIR__ . '/../bootstrap.php';

$interval = Rx\Observable::interval(1000);

$source = $interval
    ->take(4)
    ->doOnNext(function ($x) {
        echo 'Side effect', PHP_EOL;
    });

$published = $source
    ->shareReplay(3);

$published->subscribe($createStdoutObserver('SourceA '));
$published->subscribe($createStdoutObserver('SourceB '));

Rx\Observable
    ::of(true)
    ->concatMapTo(\Rx\Observable::timer(6000))
    ->flatMap(function () use ($published) {
        return $published;
    })
    ->subscribe($createStdoutObserver('SourceC '));
