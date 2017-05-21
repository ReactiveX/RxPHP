<?php

use Rx\Observable;

$source = Observable::range(0, 25)
    ->merge(Observable::range(0, 25));

return function() use ($source) {
    return $source;
};
