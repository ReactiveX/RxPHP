<?php

require_once __DIR__.'/../bootstrap.php';


$source = \Rx\Observable\BaseObservable::defer(function () {
    return \Rx\Observable\BaseObservable::just(42);
});

$subscription = $source->subscribe($stdoutObserver);

//Next value: 42
//Complete!



/* Using a promise */
$source2 = \Rx\Observable\BaseObservable::defer(function () {
    $q = new \React\Promise\Deferred();
    $q->resolve(56);

    return $q->promise();
});

$subscription = $source2->subscribe($createStdoutObserver());


//Next value: 56
//Complete!