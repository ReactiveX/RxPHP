<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = new Rx\Observable\ReturnObservable(42, \Rx\Scheduler::getDefault());
$observable->subscribe($stdoutObserver);
