<?php

use Rx\Observable;

$source = Observable::emptyObservable()
    ->defaultIfEmpty(Observable::just(25));

return function() use ($source) {
    return $source;
};
