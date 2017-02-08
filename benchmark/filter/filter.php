<?php

use Rx\Observable;

return function() use ($dummyObserver) {
    Observable::range(0, 50)
        ->filter(function($value) {
            return $value % 2 == 0;
        })
        ->filter(function($value) {
            return $value % 10 == 0;
        })
        ->subscribe($dummyObserver);
};