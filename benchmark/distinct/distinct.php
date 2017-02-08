<?php

use Rx\Observable;

$source = array_map(function($val) {
    return $val % 3;
}, range(0, 25));

return function() use ($source, $dummyObserver) {
    Observable::fromArray($source)
        ->distinct()
        ->subscribe($dummyObserver);
};