<?php

require_once __DIR__ . '/../bootstrap.php';

use Rx\Observable\ArrayObservable;

$observable = Rx\Observable::fromArray([1, 1, 2, 3, 5, 8, 13]);
$observable
    ->skip(3)
    ->subscribe($stdoutObserver);
