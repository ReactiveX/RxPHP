<?php

use Rx\Observable;

$source = Observable::range(0, 25)
    ->scan(function($acc, $x) {
        return $x + $x;
    });

return function() use ($source) {
    return $source;
};
