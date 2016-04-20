<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::range(1, 3)
    ->repeat(3);

$subscription = $source->subscribe($createStdoutObserver());
