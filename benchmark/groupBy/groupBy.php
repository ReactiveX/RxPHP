<?php

use Rx\Observable;

$source = Observable::range(0, 25)
    ->map(function($i) {
        return ['key' => $i % 5];
    })
    ->groupBy(function($item) {
        return $item['key'];
    });

return function() use ($source) {
    return $source;
};