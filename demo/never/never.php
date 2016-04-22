<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = \Rx\Observable::never();
$observable->subscribe($stdoutObserver);
