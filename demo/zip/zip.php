<?php

use Rx\Observer\CallbackObserver;

require_once __DIR__ . '/../bootstrap.php';

//Without a result selector
$range = \Rx\Observable::fromArray(range(0, 4));

$source = $range
    ->zip([
        $range->skip(1),
        $range->skip(2)
    ]);

$observer = $createStdoutObserver();

$subscription = $source
    ->subscribe(new CallbackObserver(
        function ($array) use ($observer) {
            $observer->onNext(json_encode($array));
        },
        [$observer, 'onError'],
        [$observer, 'onCompleted']
    ));
