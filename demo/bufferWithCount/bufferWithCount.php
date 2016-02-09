<?php

require_once __DIR__ . '/../bootstrap.php';

/* Without a skip parameter */
$source = Rx\Observable::range(1, 6)
    ->bufferWithCount(2);

$subscription = $source->subscribe(new \Rx\Observer\CallbackObserver(
    function ($x) {
        echo "Next: " . json_encode($x) . "\n";
    },
    function ($e) {
        echo "Error: " . $e->getMessage() . "\n";
    },
    function () {
        echo "Completed\n";
    }
));

// => Next: [1,2]
// => Next: [3,4]
// => Next: [5,6]
// => Completed

/* Using a skip */
$source = Rx\Observable::range(1, 6)
    ->bufferWithCount(2, 1);

$subscription = $source->subscribe(new \Rx\Observer\CallbackObserver(
    function ($x) {
        echo "Next: " . json_encode($x) . "\n";
    },
    function ($e) {
        echo "Error: " . $e->getMessage() . "\n";
    },
    function () {
        echo "Completed\n";
    }
));

// => Next: [1,2]
// => Next: [2,3]
// => Next: [3,4]
// => Next: [4,5]
// => Next: [5,6]
// => Next: [6]
// => Completed
