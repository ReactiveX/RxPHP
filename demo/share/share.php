<?php

require_once __DIR__ . '/../bootstrap.php';

//With Share
$source = \Rx\Observable::interval(1000)
    ->take(2)
    ->doOnNext(function ($x) {
        echo "Side effect\n";
    });

$published = $source->share();

$published->subscribe($createStdoutObserver('SourceA '));
$published->subscribe($createStdoutObserver('SourceB '));
