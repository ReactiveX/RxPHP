<?php

use Rx\Observable;

return function() use ($dummyObserver) {
    Observable::range(0, 25)
        ->zip([Observable::range(0, 25)], function($a, $b) {
            return $a + $b;
        })
        ->subscribe($dummyObserver);
};