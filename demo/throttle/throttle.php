<?php

use Rx\Observable;

require_once __DIR__ . '/../bootstrap.php';

$times = [
    ['value' => 0, 'time' => 10],
    ['value' => 1, 'time' => 200],
    ['value' => 2, 'time' => 400],
    ['value' => 3, 'time' => 500],
    ['value' => 4, 'time' => 900]
];

// Delay each item by time and project value;
$source = Observable::fromArray($times)
    ->flatMap(function ($item) {
        return Observable::of($item['value'])
            ->delay($item['time']);
    })
    ->throttle(300 /* ms */);

$subscription = $source->subscribe($stdoutObserver);
