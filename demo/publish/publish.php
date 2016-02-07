<?php

require_once __DIR__ . '/../bootstrap.php';

/* Without publish */
$interval = \Rx\Observable::range(0, 10);


$source = $interval
    ->take(2)
    ->doOnNext(function ($x) {
        echo "Side effect\n";
    });

$source->subscribe($createStdoutObserver('SourceA '));
$source->subscribe($createStdoutObserver('SourceB '));

//Side effect
//SourceA Next value: 0
//Side effect
//SourceA Next value: 1
//SourceA Complete!


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

//Side effect
//SourceC Next value: 0
//SourceD Next value: 0
//Side effect
//SourceC Next value: 1
//SourceD Next value: 1
//SourceC Complete!
//SourceD Complete!
