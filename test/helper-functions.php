<?php

use Rx\Testing\Recorded;
use Rx\Testing\Subscription;
use Rx\Notification\OnCompletedNotification;
use Rx\Notification\OnErrorNotification;
use Rx\Notification\OnNextNotification;

function onError($dueTime, $error, $comparer = null) {
    return new Recorded($dueTime, new OnErrorNotification($error), $comparer);
}

function onNext($dueTime, $value, $comparer = null) {
    return new Recorded($dueTime, new OnNextNotification($value), $comparer);
}

function onCompleted($dueTime, $comparer = null) {
    return new Recorded($dueTime, new OnCompletedNotification(), $comparer);
}

function subscribe($start, $end = null) {
    return new Subscription($start, $end);
}

function RxIdentity($x) {
    return $x;
}
