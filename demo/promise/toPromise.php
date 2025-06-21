<?php

require_once __DIR__ . '/../bootstrap.php';

$promise = \Rx\Observable::of(42)
    ->toPromise();

$promise->then(function ($value): void {
    echo "Value: {$value}\n";
});
