<?php

require_once __DIR__ . '/../bootstrap.php';


$source = \Rx\Observable::range(1, 5)
    ->takeWhileWithIndex(function ($i) {
        return $i < 3;
    });

$subscription = $source->subscribe($stdoutObserver);

