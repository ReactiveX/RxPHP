<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = Rx\Observable::fromArray([21, 42, 84]);
$observable
    ->filter(function ($elem) {
        return $elem >= 42;
    })
    ->subscribe($stdoutObserver);
