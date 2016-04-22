<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::range(0, 3)
    ->doOnEach(new \Rx\Observer\CallbackObserver(
        function ($x) {
            echo 'Do Next:', $x, PHP_EOL;
        },
        function (Exception $err) {
            echo 'Do Error:', $err->getMessage(), PHP_EOL;
        },
        function () {
            echo 'Do Completed', PHP_EOL;
        }
    ));

$subscription = $source->subscribe($stdoutObserver);