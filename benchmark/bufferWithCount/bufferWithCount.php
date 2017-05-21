<?php

use Rx\Observable;

$source = Observable::range(0, 25)
    ->bufferWithCount(5);

return function() use ($source) {
    return $source;
};
