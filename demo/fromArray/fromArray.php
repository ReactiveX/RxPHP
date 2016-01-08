<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::fromArray([1, 2, 3, 4]);

$subscription = $source->subscribe($stdoutObserver);

//Next value: 1
//Next value: 2
//Next value: 3
//Next value: 4
//Complete!
