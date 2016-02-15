<?php

require_once __DIR__ . '/../bootstrap.php';

$source = Rx\Observable::range(1, 6)
    ->bufferWithCount(2, 1)
    ->subscribe($stdoutObserver);
