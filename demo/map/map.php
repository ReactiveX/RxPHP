<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = \Rx\Observable::fromArray([21, 42]);
$observable
    ->map(function ($elem) {
        return $elem * 2;
    })
    ->subscribe($stdoutObserver);
