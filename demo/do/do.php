<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::range(0, 3)
    ->do(
        function ($x) {
            echo 'Do Next:', $x, PHP_EOL;
        },
        function (Throwable $err) {
            echo 'Do Error:', $err->getMessage(), PHP_EOL;
        },
        function () {
            echo 'Do Completed', PHP_EOL;
        }
    );

$subscription = $source->subscribe($stdoutObserver);