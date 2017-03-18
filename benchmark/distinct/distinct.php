<?php

use Rx\Observable;

$range = array_map(function($val) {
    return $val % 3;
}, range(0, 25));

$source = Observable::fromArray($range)
    ->distinct();

return function() use ($source) {
    return $source;
};
