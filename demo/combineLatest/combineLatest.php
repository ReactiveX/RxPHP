<?php

require_once __DIR__ . '/../bootstrap.php';

/* Have staggering intervals */
$source1 = \Rx\Observable::interval(100);
$source2 = \Rx\Observable::interval(120);

$source = $source1->combineLatest([$source2], function ($value1, $value2) {
    return "First: {$value1}, Second: {$value2}";
})->take(4);

$subscription = $source->subscribe($stdoutObserver);
