<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::range(1, 3)
    ->repeat(3);

$subscription = $source->subscribe($createStdoutObserver());

//Next value: 1
//Next value: 2
//Next value: 3
//Next value: 1
//Next value: 2
//Next value: 3
//Next value: 1
//Next value: 2
//Next value: 3
//Complete!
