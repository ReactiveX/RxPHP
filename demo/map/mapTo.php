<?php

require_once __DIR__ . '/../bootstrap.php';

$subscription = Rx\Observable::fromArray([21, 42])
    ->mapTo(1)
    ->subscribe($stdoutObserver);
