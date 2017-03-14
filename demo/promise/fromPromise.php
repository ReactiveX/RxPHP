<?php

require_once __DIR__ . '/../bootstrap.php';

$promise = \React\Promise\resolve(42);

$source = \Rx\Observable::fromPromise($promise);

$subscription = $source->subscribe($stdoutObserver);
