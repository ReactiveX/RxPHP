<?php

use Rx\Observable;

$source = Observable::range(0, 50)
    ->skip(25);

return function() use ($source) {
    return $source;
};