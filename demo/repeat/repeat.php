<?php

require_once __DIR__.'/../bootstrap.php';

$source = new \Rx\Observable\ArrayObservable(range(1, 3));

$subscription = $source->repeat(3)->subscribe($createStdoutObserver());

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
