<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::fromArray(range(1, 10));

$subscription = $source->count()->subscribe($stdoutObserver);
