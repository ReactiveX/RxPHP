<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::interval(1000)
    ->take(2)
    ->doOnNext(function ($x) {
        echo "Side effect\n";
    });

$published = $source->shareValue(42);

$published->subscribe($createStdoutObserver('SourceA '));
$published->subscribe($createStdoutObserver('SourceB '));
