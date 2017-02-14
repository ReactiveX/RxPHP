<?php

use Rx\Observable;
use Rx\Testing\Recorded;
use Rx\Testing\Subscription;
use Rx\Notification\OnCompletedNotification;
use Rx\Notification\OnErrorNotification;
use Rx\Notification\OnNextNotification;
use Rx\Notification\OnNextObservableNotification;

function onError($dueTime, $error, $comparer = null) {
    return new Recorded($dueTime, new OnErrorNotification($error), $comparer);
}

function onNext($dueTime, $value, $comparer = null) {
    if ($value instanceof Observable) {
        $notification = new OnNextObservableNotification($value);
    } else {
        $notification = new OnNextNotification($value);
    }
    return new Recorded($dueTime, $notification, $comparer);
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
