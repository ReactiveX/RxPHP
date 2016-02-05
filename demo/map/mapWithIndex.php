<?php

require_once __DIR__ . '/../bootstrap.php';

$subscriptions = Rx\Observable::fromArray([21, 42])
    ->mapWithIndex(function ($index, $elem) {
        return $index + $elem;
    })
    ->subscribe($stdoutObserver);


//Next value: 21
//Next value: 43
//Complete!
