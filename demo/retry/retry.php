<?php
require_once __DIR__ . '/../bootstrap.php';

$count = 0;

$observable = Rx\Observable::interval(1000)
    ->flatMap(function ($x) use (&$count) {
        if (++$count < 2) {
            return Rx\Observable::error(new \Exception('Something'));
        }
        return Rx\Observable::of(42);
    })
    ->retry(3)
    ->take(1);

$observable->subscribe($stdoutObserver);

