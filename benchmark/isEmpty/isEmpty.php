<?php

use Rx\Observable;

$source = Observable::just(25)
    ->isEmpty();

return function() use ($source) {
    return $source;
};