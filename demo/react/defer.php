<?php

require_once __DIR__.'/../bootstrap.php';

/* Using a promise */
$source = \Rx\React\PromiseFactory::toObservable(function () {
    return \Rx\React\Promise::resolved(42);
});

$subscription = $source->subscribe($createStdoutObserver());

//Next value: 42
//Complete!
