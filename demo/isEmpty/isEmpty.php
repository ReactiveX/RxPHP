<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::emptyObservable()
    ->isEmpty();

$source->subscribe($stdoutObserver);
