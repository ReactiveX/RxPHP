<?php

require_once __DIR__ . '/../bootstrap.php';

/* Have staggering intervals */
$source1 = \Rx\Observable::interval(140)
    ->map(function ($i) {
        return 'First: ' . $i;
    });
$source2 = \Rx\Observable::interval(50)
    ->map(function ($i) {
        return 'Second: ' . $i;
    });

$source3 = \Rx\Observable::interval(100)
    ->map(function ($i) {
        return 'Third: ' . $i;
    });

$source = $source1->withLatestFrom([$source2, $source3], function ($value1, $value2, $value3) {
    return $value1 . ', ' . $value2 . ', ' . $value3;
})->take(4);

$source->subscribe($stdoutObserver);
