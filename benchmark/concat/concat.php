<?php

use Rx\Observable;

$source = Observable::range(0, 25)
    ->concat(Observable::range(0, 25));

return function() use ($source) {
    return $source;
};
