<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = Rx\Observable::fromArray([1, 1, 2, 3, 5, 8, 13]);

$observable
    ->elementAt(23)
    ->subscribe($stdoutObserver);