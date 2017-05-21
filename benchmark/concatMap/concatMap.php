<?php

use Rx\Observable;

$source = Observable::range(1, 25)
    ->concatMap(function($x) {
        return Observable::range($x, 25);
    });

return function() use ($source) {
    return $source;
};
