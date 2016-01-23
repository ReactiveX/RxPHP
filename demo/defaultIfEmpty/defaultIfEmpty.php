<?php

require_once __DIR__ . '/../bootstrap.php';

$source = (new \Rx\Observable\EmptyObservable())->defaultIfEmpty(new \Rx\Observable\ReturnObservable("something"));

$subscription = $source->subscribe($stdoutObserver);

// => Next value: something
// => Completed