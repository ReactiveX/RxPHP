<?php

require_once __DIR__ . '/../bootstrap.php';

$range = \Rx\Observable::fromArray(range(0, 1000));

$source = $range
    ->take(2)
    ->doOnNext(function ($x) {
        echo "Side effect\n";
    });

$published = $source->publishValue(42);

$published->subscribe($createStdoutObserver('SourceA'));
$published->subscribe($createStdoutObserver('SourceB'));

$connection = $published->connect();
