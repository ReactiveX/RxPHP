<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::just(1)
    ->isEmpty();

$source->subscribe($stdoutObserver);
