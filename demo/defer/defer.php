<?php

require_once __DIR__.'/../bootstrap.php';


$source = \Rx\Observable::defer(function () {
    return \Rx\Observable::of(42);
});

$subscription = $source->subscribe($stdoutObserver);
