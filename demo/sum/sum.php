<?php

require_once __DIR__ . '/../bootstrap.php';

$source = Rx\Observable::range(1, 10)
    ->sum();

$subscription = $source->subscribe($stdoutObserver);
