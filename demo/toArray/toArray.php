<?php

use Rx\Observer\CallbackObserver;

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::fromArray([1, 2, 3, 4]);

$observer = $createStdoutObserver();

$subscription = $source->toArray()
    ->subscribe(new CallbackObserver(
        function ($array) use ($observer) {
            $observer->onNext(json_encode($array));
        },
        [$observer, "onError"],
        [$observer, "onCompleted"]
    ));
