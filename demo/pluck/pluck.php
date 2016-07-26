<?php

require_once __DIR__ . '/../bootstrap.php';

$source = Rx\Observable::fromArray([
    (object)['value' => 0],
    (object)['value' => 1],
    (object)['value' => 2]
])
    ->pluck('value');

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

// => Next: 0
// => Next: 1
// => Next: 2
// => Completed
