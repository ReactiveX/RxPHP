<?php

require_once __DIR__ . '/../bootstrap.php';

$source = Rx\Observable::of(42)
    ->repeatWhen(function (\Rx\Observable $notifications) {
        return $notifications
            ->scan(function ($acc, $x) {
                return $acc + $x;
            }, 0)
            ->delay(1000)
            ->doOnNext(function () {
                echo "1 second delay", PHP_EOL;
            })
            ->takeWhile(function ($count) {
                return $count < 2;
            });
    });

$subscription = $source->subscribe($createStdoutObserver());
