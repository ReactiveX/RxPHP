<?php

require_once __DIR__ . '/../bootstrap.php';

$observable       = Rx\Observable::of(42)->repeat();
$otherObservable  = Rx\Observable::of(21)->repeat();
$mergedObservable = $observable
    ->merge($otherObservable)
    ->take(10);

$disposable = $mergedObservable->subscribe($stdoutObserver);
