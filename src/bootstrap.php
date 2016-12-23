<?php

use Interop\Async\Loop;
use React\EventLoop\StreamSelectLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

Loop::setFactory(ReactDriverFactory::createFactoryFromLoop(StreamSelectLoop::class));

register_shutdown_function(function () {
    Loop::execute(function () {}, Loop::get());
});
