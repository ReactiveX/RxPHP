<?php

declare(strict_types = 1);

use Interop\Async\Loop;

register_shutdown_function(function () {
    Loop::execute(function () {}, Loop::get());
});
