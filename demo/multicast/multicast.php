<?php

require_once __DIR__ . '/../bootstrap.php';

$subject = new \Rx\Subject\Subject();
$source  = \Rx\Observable::range(0, 3)->multicast($subject);

$subscription = $source->subscribe($stdoutObserver);
$subject->subscribe($stdoutObserver);

$connected = $source->connect();

$subscription->dispose();

//Next value: 0
//Next value: 0
//Next value: 1
//Next value: 1
//Next value: 2
//Next value: 2
//Complete!