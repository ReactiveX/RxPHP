<?php

require_once __DIR__.'/../bootstrap.php';

$promise = new \Rx\Promise\Promise(\Rx\Observable::of(42));

$source =  \Rx\Observable::fromPromise($promise);

$subscription = $source->subscribe($stdoutObserver);