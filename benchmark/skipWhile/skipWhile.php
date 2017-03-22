<?php

use Rx\Observable;

$source = Observable::range(0, 50)
    ->skipWhile(function($value) {
        return $value < 25;
    });

return function() use ($source) {
    return $source;
};
