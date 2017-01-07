<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::range(1, 3)
    ->flatMapLatest(function ($x) {
        return \Rx\Observable::fromArray([$x . 'a', $x . 'b']);
    });

$source->subscribe($stdoutObserver);
