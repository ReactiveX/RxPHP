<?php

require_once __DIR__ . '/../bootstrap.php';

/* With publish */
$interval = \Rx\Observable::range(0, 10);

$source = $interval
    ->take(2)
    ->doOnNext(function ($x) {
        echo "Side effect\n";
    });

$published = $source->publish();

$published->subscribe($createStdoutObserver('SourceC '));
$published->subscribe($createStdoutObserver('SourceD '));

$published->connect();
