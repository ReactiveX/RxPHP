<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::fromArray([
    42, 24, 42, 24
])->distinct();

$subscription = $source->subscribe($stdoutObserver);


//Next value: 42
//Next value: 24
//Complete!
