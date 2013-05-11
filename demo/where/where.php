<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = new Rx\Observable\ArrayObservable(array(21, 42, 84));
$observable
    ->where(function($elem) { return $elem >= 42; })
    ->subscribe($stdoutObserver);
