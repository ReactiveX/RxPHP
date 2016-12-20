<?php

require_once __DIR__ . '/../bootstrap.php';

$promise = \Rx\Observable::of(42)
    ->toPromise();

$promise->when(function (Exception $ex = null, $value) {
    echo "Value: {$value}\n";
});
