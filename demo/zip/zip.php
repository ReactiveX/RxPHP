<?php

use Rx\Observer\CallbackObserver;

require_once __DIR__ . '/../bootstrap.php';

//Without a result selector
$range = \Rx\Observable\BaseObservable::fromArray(range(0, 4));

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
        [$observer, "onError"],
        [$observer, "onCompleted"]
    ));

// Next value: [0,1,2]
// Next value: [1,2,3]
// Next value: [2,3,4]
// Complete!


//With a result selector
$range = \Rx\Observable\BaseObservable::fromArray(range(0, 4));

$source = $range
    ->zip([
        $range->skip(1),
        $range->skip(2)
    ], function ($s1, $s2, $s3) {
        return $s1 . ':' . $s2 . ':' . $s3;
    });

$observer = $createStdoutObserver();

$subscription = $source->subscribe($createStdoutObserver());
