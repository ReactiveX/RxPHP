<?php

require_once __DIR__ . '/../bootstrap.php';

$source = Rx\Observable::range(0, 9)->average();

$subscription = $source->subscribe($stdoutObserver);
