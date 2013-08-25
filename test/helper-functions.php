<?php

use Rx\Testing\Recorded;
use Rx\Testing\Subscription;
use Rx\Notification\OnCompletedNotification;
use Rx\Notification\OnErrorNotification;
use Rx\Notification\OnNextNotification;

function onError($dueTime, $error) {
    return new Recorded($dueTime, new OnErrorNotification($error));
}

function onNext($dueTime, $value) {
    return new Recorded($dueTime, new OnNextNotification($value));
}

function onCompleted($dueTime) {
    return new Recorded($dueTime, new OnCompletedNotification());
}

function subscribe($start, $end = null) {
    return new Subscription($start, $end);
}

function RxIdentity($x) {
    return $x;
}
