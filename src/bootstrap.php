<?php

use Interop\Async\Loop;
use React\EventLoop\StreamSelectLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

(function () {
    $driver = ReactDriverFactory::createFactoryFromLoop(StreamSelectLoop::class);
    Loop::setFactory($driver);

    register_shutdown_function(function () use (&$hasBeenRun) {
        if (!$hasBeenRun) {
            Loop::get()->run();
        }
    });

    Loop::get()->defer(function () use (&$hasBeenRun) {
        $hasBeenRun = true;
    });
})();
