<?php

use Rx\Observable;

$source = Observable::range(0, 25)
    ->map(function() {
        return Observable::range(0, 25);
    })
    ->mergeAll();

return function() use ($source) {
    return $source;
};
