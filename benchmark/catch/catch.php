<?php

use Rx\Observable;

$source = Observable::error(new \Exception('error'))
    ->catchError(function() {
        return Observable::just(25);
    });

return function() use ($source) {
    return $source;
};
