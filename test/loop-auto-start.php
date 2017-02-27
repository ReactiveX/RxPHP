<?php

declare(strict_types = 1);

use React\EventLoop\Factory;
use Rx\Scheduler;

$loop      = Factory::create();
$scheduler = new Scheduler\EventLoopScheduler($loop);
Scheduler::setAsync($scheduler);

register_shutdown_function(function () use ($loop) {
    $loop->run();
});
