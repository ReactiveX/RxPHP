<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = new Rx\Observable\ArrayObservable(array(21, 42, 21, 42, 21, 42));
$observable
    ->groupBy(
        function($elem) {
            if ($elem === 42) {
                return 0;
            }

            return 1;
        },
        null,
        function($key){ return $key; }
    )
    ->subscribeCallback(function($groupedObserver) use ($createStdoutObserver) {
        $groupedObserver->subscribe($createStdoutObserver($groupedObserver->getKey() . ": "));
    });
