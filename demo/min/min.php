<?php

require_once __DIR__ . "/../bootstrap.php";

/* Without comparer */
$source = \Rx\Observable::fromArray([1, 3, 5, 7, 9, 2, 4, 6, 8])
    ->min();

$subscription = $source->subscribe($createStdoutObserver());
