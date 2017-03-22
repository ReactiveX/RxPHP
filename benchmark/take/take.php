<?php

use Rx\Observable;

$source = Observable::range(0, 50)
    ->take(5);

return function() use ($source) {
    return $source;
};