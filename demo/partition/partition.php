<?php

require_once __DIR__ . '/../bootstrap.php';

list($evens, $odds) = \Rx\Observable::range(0, 10, \Rx\Scheduler::getImmediate())
    ->partition(function ($x) {
        return $x % 2 === 0;
    });

//Because we used the immediate scheduler with range, the subscriptions are not asynchronous.
$evens->subscribe($createStdoutObserver('Evens '));
$odds->subscribe($createStdoutObserver('Odds '));
