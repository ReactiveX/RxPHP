<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = new Rx\Observable\EmptyObservable();
$observable->subscribe($stdoutObserver);
