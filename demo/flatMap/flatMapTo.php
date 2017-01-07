<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = Rx\Observable::range(1, 5);

$selectManyObservable = $observable->flatMapTo(\Rx\Observable::range(0, 2));

$disposable = $selectManyObservable->subscribe($stdoutObserver);
