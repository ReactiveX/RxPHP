<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::fromArray([
    ['id' => '24'],
    ['id' => '42'],
    ['id' => '24'],
    ['id' => '24']
])
    ->distinctUntilKeyChanged(function ($x) {
        return $x['id'];
    })
    ->map(function ($x) {
        return $x['id'];
    });

$subscription = $source->subscribe($stdoutObserver);
