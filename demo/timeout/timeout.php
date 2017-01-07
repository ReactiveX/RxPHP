<?php

require_once __DIR__ . '/../bootstrap.php';

Rx\Observable::interval(1000)
    ->timeout(500)
    ->subscribe($createStdoutObserver('One second - '));

Rx\Observable::interval(100)
    ->take(3)
    ->timeout(500)
    ->subscribe($createStdoutObserver('100 ms     - '));
