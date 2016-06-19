<?php

require_once __DIR__ . "/../bootstrap.php";

/*
 * By specification
 * true == 1
 * false == null == 0 
 */
$source = \Rx\Observable::fromArray(["3s", 5e1, true, false, null, 4, 6, 8])
    ->average(\Rx\Operator\AverageOperator::UNEXPECTED_VALUE_CAST);

$subscription = $source->subscribe($createStdoutObserver());
