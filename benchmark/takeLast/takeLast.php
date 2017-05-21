<?php

use Rx\Observable;

$source = Observable::range(0, 500)
    ->takeLast(50);

return function() use ($source) {
    return $source;
};