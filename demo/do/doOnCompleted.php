<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::emptyObservable()
    ->doOnCompleted(function () {
        echo 'Do Completed', PHP_EOL;
    });

$subscription = $source->subscribe($stdoutObserver);