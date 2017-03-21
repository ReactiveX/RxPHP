<?php

use Rx\Observable;

$source = Observable::range(1, 25)
    ->concat(Observable::range(1, 25));

return function() use ($source) {
    return $source;
};
