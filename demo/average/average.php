<?php

require_once __DIR__ . '/../bootstrap.php';

$source = Rx\Observable::range(0, 9)->average();

$subscription = $source->subscribeCallback(
    function ($x) {
        echo 'Next: ' . $x . PHP_EOL;
    },
    function ($err) {
        echo 'Error: ' . $err . PHP_EOL;
    },
    function () {
        echo 'Completed' . PHP_EOL;
    }
);

// => Next: 4
// => Completed
