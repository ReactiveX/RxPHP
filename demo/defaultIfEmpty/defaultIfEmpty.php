<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::emptyObservable()->defaultIfEmpty(Rx\Observable::just('something'));

$subscription = $source->subscribe($stdoutObserver);
