<?php

require_once __DIR__.'/../bootstrap.php';

// With React Promise
$source = \Rx\Observable::just(42);
$promise = \Rx\React\Promise::fromObservable($source);

$promise->then(function ($value) {
    echo "Value {$value}\n";
});

//Value 42



