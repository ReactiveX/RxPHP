<?php

require_once __DIR__ . '/../bootstrap.php';

//Without a seed
$source = \Rx\Observable\BaseObservable::fromArray(range(1, 3));

$subscription = $source
    ->scan(function ($acc, $x) {
        return $acc + $x;
    })
    ->subscribe($createStdoutObserver());

//Next value: 1
//Next value: 3
//Next value: 6
//Complete!

//With a seed
$source = \Rx\Observable\BaseObservable::fromArray(range(1, 3));

$subscription = $source
    ->scan(function ($acc, $x) {
        return $acc * $x;
    }, 1)
    ->subscribe($createStdoutObserver());

//Next value: 1
//Next value: 2
//Next value: 6
//Complete!
