<?php

use Rx\Observable;

$source = Observable::range(0, 25)
    ->startWithArray([5, 5, 5]);

return function() use ($source) {
    return $source;
};