<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::range(0, 5)
    ->takeLast(3);

$source->subscribe($stdoutObserver);
