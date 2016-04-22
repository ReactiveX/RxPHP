<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = \Rx\Observable::range(0, 3);

$observable->subscribe($stdoutObserver);
