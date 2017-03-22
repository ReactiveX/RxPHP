<?php

use Rx\Observable;

$source = Observable::just([1, 2, 3, 4, 5])
    ->pluck(2);

return function() use ($source) {
    return $source;
};
