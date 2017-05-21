<?php

use Rx\Observable;

$source = Observable::range(0, 500)
    ->skipLast(50);

return function() use ($source) {
    return $source;
};
