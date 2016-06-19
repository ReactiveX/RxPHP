<?php

require_once __DIR__ . "/../bootstrap.php";

/*
 * By specification
 * true == 1
 * false == null == 0 
 */
$source = \Rx\Observable::fromArray(["4", 5e1, true, false, null, 2, 6, 8])
    ->average(\Rx\Operator\AverageOperator::UNEXPECTED_VALUE_IGNORE);

$subscription = $source->subscribe($createStdoutObserver());