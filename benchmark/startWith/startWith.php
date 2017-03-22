<?php

use Rx\Observable;

$source = Observable::range(0, 25)
    ->startWith(5);

return function() use ($source) {
    return $source;
};