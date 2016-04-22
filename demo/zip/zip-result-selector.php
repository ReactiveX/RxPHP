<?php

require_once __DIR__ . '/../bootstrap.php';

//With a result selector
$range = \Rx\Observable::fromArray(range(0, 4));

$source = $range
    ->zip([
        $range->skip(1),
        $range->skip(2)
    ], function ($s1, $s2, $s3) {
        return $s1 . ':' . $s2 . ':' . $s3;
    });

$observer = $createStdoutObserver();

$subscription = $source->subscribe($createStdoutObserver());
