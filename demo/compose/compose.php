<?php

use React\EventLoop\Factory;
use Rx\Observable;
use Rx\Scheduler\EventLoopScheduler;

require_once __DIR__ . '/../bootstrap.php';

$process = function ($observable) {
    return $observable
        ->filter(function ($val) { return $val % 2 == 0; })
        ->map(function ($val) { return $val * 2; });
};

$source = Observable::fromArray(range(1, 10))
        ->compose($process);

$subscription = $source->subscribe($stdoutObserver);

