<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = new Rx\Observable\ThrowObservable(new Exception('Oops!'));
$observable->subscribe($stdoutObserver);
