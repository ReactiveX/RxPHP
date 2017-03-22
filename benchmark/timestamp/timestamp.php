<?php

use Rx\Observable;

$source = Observable::range(0, 5)
    ->timestamp();

return function() use ($source) {
    return $source;
};
