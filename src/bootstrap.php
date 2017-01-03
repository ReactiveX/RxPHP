<?php

use Interop\Async\Loop;

register_shutdown_function(function () {
    Loop::execute(function () {}, Loop::get());
});
