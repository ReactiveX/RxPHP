<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::fromArray([
    24, 42, 24, 24
])->distinctUntilChanged();

$subscription = $source->subscribe($stdoutObserver);
