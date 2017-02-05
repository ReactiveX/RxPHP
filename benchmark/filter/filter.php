<?php

use Rx\Observable;

return function() use ($dummyObserver) {
    Observable::range(1, pow(10, 3))
        ->filter(function($value) {
            return $value % 2 == 0;
        })
        ->subscribe($dummyObserver);
};