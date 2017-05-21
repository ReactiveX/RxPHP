<?php

use Rx\Observable;

$source = Observable::defer(function() {
    return Observable::forkJoin([
        Observable::just(25),
        Observable::range(0, 25),
        Observable::fromArray(([1, 2, 3, 4, 5]))
    ]);
});

return function() use ($source) {
    return $source;
};
