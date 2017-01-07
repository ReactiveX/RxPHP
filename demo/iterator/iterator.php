<?php

require_once __DIR__ . '/../bootstrap.php';

$generator = function () {
    for ($i = 1; $i <= 3; $i++) {
        yield $i;
    }

    return 4;
};

$source = Rx\Observable::fromIterator($generator());

$source->subscribe($stdoutObserver);
