<?php

use Rx\Observable;

$source = Observable::range(0, 25)
    ->flatMap(function($x) {
        return Observable::range($x, 25);
    });

return function() use ($source) {
    return $source;
};
