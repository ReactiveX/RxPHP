<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = new Rx\Observable\ReturnObservable(42);
$observable->subscribe($stdoutObserver);
