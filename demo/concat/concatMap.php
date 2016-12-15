<?php

require_once __DIR__ . '/../bootstrap.php';

$source = Rx\Observable::range(0, 5)
    ->concatMap(function ($x, $i) {
        return \Rx\Observable::interval(100)
            ->take($x)
            ->map(function () use ($i) {
                return $i;
            });
    });

$subscription = $source->subscribe($stdoutObserver);
