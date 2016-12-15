<?php

require_once __DIR__ . '/../bootstrap.php';

$source = Rx\Observable::race(
    [
        Rx\Observable::timer(500)->map(function () {
            return 'foo';
        }),
        Rx\Observable::timer(200)->map(function () {
            return 'bar';
        })
    ]
);

$source->subscribe($stdoutObserver);
