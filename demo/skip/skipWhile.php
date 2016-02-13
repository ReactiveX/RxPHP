<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = Rx\Observable::range(1, 5)
    ->skipWhile(function ($x) {
        return $x < 3;
    });

$observable->subscribe($stdoutObserver);
