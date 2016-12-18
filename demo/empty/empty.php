<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = \Rx\Observable::empty();
$observable->subscribe($stdoutObserver);
