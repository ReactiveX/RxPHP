<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = \Rx\Observable::range(0, 3);

$observable->subscribe($stdoutObserver);

//Next value: 0
//Next value: 1
//Next value: 2
//Complete!
