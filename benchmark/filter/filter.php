<?php

use Rx\Observable;

$source = Observable::range(0, 50)
    ->filter(function($value) {
        return $value % 2 == 0;
    })
    ->filter(function($value) {
        return $value % 10 == 0;
    });

return function() use ($source) {
    return $source;
};
