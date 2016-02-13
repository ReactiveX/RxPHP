<?php

require_once __DIR__ . '/../bootstrap.php';

$obs2 = Rx\Observable::just(42);

$source = \Rx\Observable::error(new Exception("Some error"))
    ->catchError(function (Exception $e, \Rx\Observable $sourceObs) use ($obs2) {
        return $obs2;
    });

$subscription = $source->subscribe($stdoutObserver);