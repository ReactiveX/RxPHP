<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = Rx\Observable::range(1, 5)
    ->skipWhileWithIndex(function ($i, $value) {
        return $i < 3;
    });

$observable->subscribe($stdoutObserver);
