<?php

require_once __DIR__.'/../bootstrap.php';


// Create a promise which resolves 42
$q = new \React\Promise\Deferred();
$q->resolve(42);

$source = \Rx\Observable\BaseObservable::fromPromise($q->promise());

$subscription = $source->subscribe($stdoutObserver);

//Next value: 42
//Complete!



// Create a promise which rejects with an error
$q = new \React\Promise\Deferred();
$q->reject(new Exception('because'));

$source2 = \Rx\Observable\BaseObservable::fromPromise($q->promise());

$subscription = $source2->subscribe($createStdoutObserver());

//Exception: because
