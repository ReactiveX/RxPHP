<?php

require_once __DIR__ . '/../bootstrap.php';

$loop = \React\EventLoop\Factory::create();
$scheduler = new \Rx\Scheduler\EventLoopScheduler($loop);

$source1 = \Rx\Observable::fromArray(range(0, 100));
$source2 = \Rx\Observable::fromArray(range(0, 100));

$source = $source1->combineLatest([$source2], function($value1, $value2){
    return "First: {$value1}, Second: {$value2}";
})->take(4);

$subscription = $source->subscribe($stdoutObserver, $scheduler);

$loop->run();
