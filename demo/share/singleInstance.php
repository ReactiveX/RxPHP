<?php

require_once __DIR__ . '/../bootstrap.php';

$interval = Rx\Observable::interval(1000);

$source = $interval
    ->take(2)
    ->do(function () {
        echo 'Side effect', PHP_EOL;
    });

$single = $source->singleInstance();

// two simultaneous subscriptions, lasting 2 seconds
$single->subscribe($createStdoutObserver('SourceA '));
$single->subscribe($createStdoutObserver('SourceB '));

\Rx\Observable::timer(5000)->subscribe(function () use ($single, &$createStdoutObserver) {
    // resubscribe two times again, more than 5 seconds later,
    // long after the original two subscriptions have ended
    $single->subscribe($createStdoutObserver('SourceC '));
    $single->subscribe($createStdoutObserver('SourceD '));
});
