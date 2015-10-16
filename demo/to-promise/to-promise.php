<?php

require_once __DIR__.'/../bootstrap.php';

// With React Promise
$source = \Rx\Observable\BaseObservable::just(42)->toPromise();

$source->then(function ($value) {
    echo "Value {$value}\n";
});

//Value 42



