<?php
require_once __DIR__ . '/../bootstrap.php';

\Rx\Observable::interval(1000)
    ->doOnNext(function ($x) {
        echo 'Side effect: ' . $x . "\n";
    })
    ->delay(500)
    ->take(5)
    ->subscribe($createStdoutObserver());
