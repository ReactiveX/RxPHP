<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::of(2)
    ->startWith(1);

$subscription = $source->subscribe($stdoutObserver);
