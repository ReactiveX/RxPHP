<?php

use Rx\Observable;

return function() use ($dummyObserver) {
    Observable::range(1, pow(10, 3))
        ->distinct()
        ->subscribe($dummyObserver);
};