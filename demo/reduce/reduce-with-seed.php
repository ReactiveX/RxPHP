<?php

require_once __DIR__ . '/../bootstrap.php';

//With a seed
$source = \Rx\Observable::fromArray(range(1, 3));

$subscription = $source
    ->reduce(function ($acc, $x) {
        return $acc * $x;
    }, 1)
    ->subscribe($createStdoutObserver());
