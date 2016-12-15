<?php

require_once __DIR__ . '/../bootstrap.php';

$obs = \Rx\Observable::interval(100)
    ->take(3)
    ->mapWithIndex(function ($i) {
        return $i;
    });

$source = Rx\Observable::range(0, 5)
    ->concatMapTo($obs);

$subscription = $source->subscribe($stdoutObserver);
