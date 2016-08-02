<?php

require_once __DIR__ . '/../bootstrap.php';

$source = Rx\Observable::fromArray([
    (object)['value' => 0],
    (object)['value' => 1],
    (object)['value' => 2]
])
    ->pluck('value');

$subscription = $source->subscribe($stdoutObserver);
