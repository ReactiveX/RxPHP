<?php

require_once __DIR__ . '/../bootstrap.php';

$observable       = Rx\Observable::just(42)->repeat();
$otherObservable  = Rx\Observable::just(21)->repeat();
$mergedObservable = $observable
    ->merge($otherObservable)
    ->take(10);

$disposable = $mergedObservable->subscribe($stdoutObserver);
