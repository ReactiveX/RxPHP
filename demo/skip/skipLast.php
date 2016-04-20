<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = Rx\Observable::range(0, 5)
    ->skipLast(3);

$observable->subscribe($stdoutObserver);
