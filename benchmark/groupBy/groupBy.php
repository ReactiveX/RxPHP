<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$source = Observable::range(0, 25, $scheduler)
    ->map(function($i) {
        return ['key' => $i % 5];
    })
    ->groupBy(function($item) {
        return $item['key'];
    });

return function() use ($source) {
    return $source;
};