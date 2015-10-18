<?php

require_once __DIR__.'/../bootstrap.php';


$source = \Rx\Observable\BaseObservable::defer(function () {
    return \Rx\Observable\BaseObservable::just(42);
});

$subscription = $source->subscribe($stdoutObserver);

//Next value: 42
//Complete!
