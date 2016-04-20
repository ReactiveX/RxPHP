<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::range(0, 3)
    ->doOnNext(function ($x) {
        echo 'Do Next:', $x, PHP_EOL;
    });

$subscription = $source->subscribe($stdoutObserver);