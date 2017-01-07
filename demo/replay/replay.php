<?php

require_once __DIR__ . '/../bootstrap.php';

$interval = \Rx\Observable::interval(1000);

$source = $interval
    ->take(2)
    ->doOnNext(function ($x) {
        echo $x, ' something', PHP_EOL;
        echo 'Side effect', PHP_EOL;
    });

$published = $source
    ->replay(function (\Rx\Observable $x) {
        return $x->take(2)->repeat(2);
    }, 3);

$published->subscribe($createStdoutObserver('SourceA '));
$published->subscribe($createStdoutObserver('SourceB '));
