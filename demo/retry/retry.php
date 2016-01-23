<?php
require_once __DIR__ . '/../bootstrap.php';

$count = 0;

$observable = \Rx\Observable::fromArray(range(1, 5))
    ->flatMap(function ($x) use (&$count) {
        if (++$count < 2) {
            return new \Rx\Observable\ErrorObservable(new \Exception("Something"));
        }
        return new \Rx\Observable\ReturnObservable($x);
    })
    ->retry(3)
    ->take(1);

$observable->subscribe($stdoutObserver);
