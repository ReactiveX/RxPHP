<?php

require_once __DIR__ . "/../bootstrap.php";

/*
 * By specification
 * true == 1
 * false == null == 0 
 */
$source = \Rx\Observable::fromArray([1, "3", 5e1, true, false, null, 4, 6, 8])
    ->average();

$subscription = $source->subscribe($createStdoutObserver());
