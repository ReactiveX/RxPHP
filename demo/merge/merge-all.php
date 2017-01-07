<?php

require_once __DIR__ . '/../bootstrap.php';

$sources = Rx\Observable::range(0, 3)
    ->map(function ($x) {
        return Rx\Observable::range($x, 3);
    });

$merged = $sources->mergeAll();

$disposable = $merged->subscribe($stdoutObserver);
