<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::of(4)
    ->startWithArray([1, 2, 3]);

$subscription = $source->subscribe($stdoutObserver);
