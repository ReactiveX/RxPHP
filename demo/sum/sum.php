<?php

require_once __DIR__ . '/../bootstrap.php';

$source = Rx\Observable::range(1, 10)
    ->sum();

$subscription = $source->subscribeCallback(
    function ($x) {
        echo 'Next: ' . $x . PHP_EOL;
    },
    function ($err) {
        echo 'Error: ' . $err . PHP_EOL;
    },
    function () {
        echo 'Completed' . PHP_EOL;
    });

// => Next: 55
// => Completed
