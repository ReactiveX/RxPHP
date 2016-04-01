<?php

require_once __DIR__ . "/../bootstrap.php";

/* Without comparer */
$source = \Rx\Observable::fromArray([1,3,5,7,9,2,4,6,8])
    ->max();

$subscription = $source->subscribe($createStdoutObserver());

// => Next: 9
// => Completed

/* With a comparer */
$comparer = function ($x, $y) {
    if ($x > $y) {
        return 1;
    } elseif ($x < $y) {
        return -1;
    }
    return 0;
};

$source = \Rx\Observable::fromArray([1,3,5,7,9,2,4,6,8])
    ->max($comparer);

$subscription = $source->subscribe($createStdoutObserver());

// => Next: 9
// => Completed
