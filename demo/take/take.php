<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = new Rx\Observable\ArrayObservable(array(21, 42, 63));
$observable
    ->take(2)
    ->subscribe($stdoutObserver);
