<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = \Rx\Observable::fromArray([21, 42, 21, 42, 21, 42]);
$observable
    ->groupBy(
        function ($elem) {
            if ($elem === 42) {
                return 0;
            }

            return 1;
        },
        null,
        function ($key) {
            return $key;
        }
    )
    ->subscribe(function ($groupedObserver) use ($createStdoutObserver) {
        $groupedObserver->subscribe($createStdoutObserver($groupedObserver->getKey() . ": "));
    });
