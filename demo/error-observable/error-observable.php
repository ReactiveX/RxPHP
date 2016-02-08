<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = Rx\Observable::error(new Exception('Oops!'));
$observable->subscribe($stdoutObserver);
