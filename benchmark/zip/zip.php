<?php

use Rx\Observable;

$source = Observable::range(0, 25)
    ->zip([Observable::range(0, 25)], function ($a, $b) {
        return $a + $b;
    });

return function() use ($source) {
    return $source;
};