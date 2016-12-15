<?php

require_once __DIR__ . '/../bootstrap.php';

$i = new \Rx\Scheduler\ImmediateScheduler();
\Rx\Scheduler::setDefault($i);

list($evens, $odds) = \Rx\Observable::range(0, 10)
    ->partition(function ($x) {
        return $x % 2 === 0;
    });

$evens->subscribe($createStdoutObserver('Evens '));
$odds->subscribe($createStdoutObserver('Odds '));
