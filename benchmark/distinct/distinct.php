<?php

use Rx\Observable;

$source = Observable::range(0, 25)
    ->map(function($i) {
        return $i % 3;
    })
    ->distinct();

return function() use ($source) {
    return $source;
};
