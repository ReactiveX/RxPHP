<?php

use Rx\Observable;

return function() use ($dummyObserver) {
    Observable::range(0, 500)
        ->takeLast(50)
        ->subscribe($dummyObserver);
};