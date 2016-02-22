<?php

require_once __DIR__ . '/../bootstrap.php';


$source = \Rx\Observable::range(1, 5)
    ->takeWhile(function ($x) {
        return $x < 3;
    });

$subscription = $source->subscribe($stdoutObserver);

