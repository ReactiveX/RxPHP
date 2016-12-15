<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::interval(105)
    ->takeUntil(\Rx\Observable::timer(1000));

$subscription = $source->subscribe($stdoutObserver);

