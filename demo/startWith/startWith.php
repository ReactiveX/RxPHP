<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::just(2)
    ->startWith(1);

$subscription = $source->subscribe($stdoutObserver);
