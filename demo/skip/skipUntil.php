<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = Rx\Observable::interval(1000)
    ->skipUntil(\Rx\Observable::timer(5000))
    ->take(3);

$observable->subscribe($stdoutObserver);
