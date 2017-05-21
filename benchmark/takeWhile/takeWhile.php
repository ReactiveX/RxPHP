<?php

use Rx\Observable;

$source = Observable::range(0, 50)
    ->takeWhile(function($value) {
        return $value < 48;
    });

return function() use ($source) {
    return $source;
};
