<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = Rx\Observable::fromArray([2,3,6,7,9,3,6]);
$observable
    ->elementAt(23)
    ->subscribe($stdoutObserver);
