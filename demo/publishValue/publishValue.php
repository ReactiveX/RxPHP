<?php

require_once __DIR__ . '/../bootstrap.php';

$range = \Rx\Observable\BaseObservable::fromArray(range(0, 1000));

$source = $range
    ->take(2)
    ->doOnNext(function ($x) {
        echo "Side effect\n";
    });

$published = $source->publishValue(42);

$published->subscribe($createStdoutObserver('SourceA'));
$published->subscribe($createStdoutObserver('SourceB'));

$connection = $published->connect();

//SourceA Next value: 42
//SourceB Next value: 42
//Side effect
//SourceA Next value: 0
//SourceB Next value: 0
//Side effect
//SourceA Next value: 1
//SourceB Next value: 1
//SourceA Complete!
//SourceB Complete!
