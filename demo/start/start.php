<?php

require_once __DIR__ . '/../bootstrap.php';

$source = Rx\Observable::start(function () {
    return 42;
});

$source->subscribe($stdoutObserver);
