<?php

require_once __DIR__ . '/../bootstrap.php';

$source = Rx\Observable::range(0, 3)
    ->map(function ($x) {
        return \Rx\Observable::range($x, 3);
    })
    ->switchLatest();

$subscription = $source->subscribe($stdoutObserver);
