<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = Rx\Observable::fromArray([21, 42, 63]);
$observable
    ->take(2)
    ->subscribe($stdoutObserver);
