<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::fromArray([
    24, 42, 24, 24
])->distinctUntilChanged();

$subscription = $source->subscribe($stdoutObserver);


//Next value: 24
//Next value: 42
//Next value: 24
//Complete!
