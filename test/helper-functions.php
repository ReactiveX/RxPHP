<?php

declare(strict_types = 1);

use Rx\Testing\Recorded;
use Rx\Testing\Subscription;
use Rx\Notification\OnCompletedNotification;
use Rx\Notification\OnErrorNotification;
use Rx\Notification\OnNextNotification;

function onError(int $dueTime, $error, callable $comparer = null) {
    return new Recorded($dueTime, new OnErrorNotification($error), $comparer);
}

function onNext(int $dueTime, $value, callable $comparer = null) {
    return new Recorded($dueTime, new OnNextNotification($value), $comparer);
}

function onCompleted(int $dueTime, callable $comparer = null) {
    return new Recorded($dueTime, new OnCompletedNotification(), $comparer);
}

function subscribe(int $start, int $end = PHP_INT_MAX) {
    return new Subscription($start, $end);
}

function RxIdentity($x) {
    return $x;
}
