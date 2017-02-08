<?php

use Rx\Observable;

return function() use ($dummyObserver) {
    Observable::range(0, 500)
        ->skipLast(50)
        ->subscribe($dummyObserver);
};