<?php

use Rx\Observable;

return function() use ($dummyObserver) {
    Observable::range(0, 25)
        ->bufferWithCount(5)
        ->subscribe($dummyObserver);
};