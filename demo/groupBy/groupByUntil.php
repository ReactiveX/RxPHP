<?php

use Rx\Observer\CallbackObserver;

require_once __DIR__ . '/../bootstrap.php';

$codes = [
    ['id' => 38],
    ['id' => 38],
    ['id' => 40],
    ['id' => 40],
    ['id' => 37],
    ['id' => 39],
    ['id' => 37],
    ['id' => 39],
    ['id' => 66],
    ['id' => 65]
];

$source = Rx\Observable
    ::fromArray($codes)
    ->concatMap(function ($x) {
        return \Rx\Observable::timer(100)->mapTo($x);
    })
    ->groupByUntil(
        function ($x) {
            return $x['id'];
        },
        function ($x) {
            return $x['id'];
        },
        function ($x) {
            return Rx\Observable::timer(200);
        });

$subscription = $source->subscribe(new CallbackObserver(
    function (\Rx\Observable $obs) {
        // Print the count
        $obs->count()->subscribe(new CallbackObserver(
            function ($x) {
                echo 'Count: ', $x, PHP_EOL;
            }));
    },
    function (Throwable $err) {
        echo 'Error', $err->getMessage(), PHP_EOL;
    },
    function () {
        echo 'Completed', PHP_EOL;
    }));

