<?php

require_once __DIR__ . '/../bootstrap.php';

$source = Rx\Observable::interval(1000)
    ->map(function ($n) {
        if ($n === 2) {
            throw new Exception();
        }
        return $n;
    })
    ->retryWhen(function (\Rx\Observable $errors) {
        return $errors->delay(200);
    })
    ->take(6);

$subscription = $source->subscribe($createStdoutObserver());
