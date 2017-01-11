<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::emptyObservable()
    ->isEmpty();

$subscription = $source->subscribe($stdoutObserver);
