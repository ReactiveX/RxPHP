<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::emptyObservable()->defaultIfEmpty(new \Rx\Observable\ReturnObservable("something"));

$subscription = $source->subscribe($stdoutObserver);
