<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = Rx\Observable::range(1, 2);

$selectManyObservable = $observable->flatMap(function ($value) {
    return Rx\Observable::range($value, 2);
});

$selectManyObservable->subscribe($stdoutObserver);