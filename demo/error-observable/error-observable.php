<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = new Rx\Observable\ErrorObservable(new Exception('Oops!'));
$observable->subscribe($stdoutObserver);
