<?php

use Rx\Observable;

$source = Observable::range(0, 25)
    ->reduce(function($a, $b) {
        return $a + $b;
    });

return function() use ($source) {
    return $source;
};
