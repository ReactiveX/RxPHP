<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::error(new Exception('Oops'))
    ->doOnError(function (Throwable $err) {
        echo 'Do Error:', $err->getMessage(), PHP_EOL;
    });

$subscription = $source->subscribe($stdoutObserver);