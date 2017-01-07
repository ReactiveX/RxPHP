<?php
require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::timer(200);

$source->subscribe($createStdoutObserver());
