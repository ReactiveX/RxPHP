<?php

declare(strict_types = 1);

use Rx\Observable;
use Rx\Testing\Recorded;
use Rx\Testing\Subscription;
use Rx\Functional\FunctionalTestCase;
use Rx\Notification\OnCompletedNotification;
use Rx\Notification\OnErrorNotification;
use Rx\Notification\OnNextNotification;
use Rx\Notification\OnNextObservableNotification;

function onError(int $dueTime, $error, callable $comparer = null)
{
    return new Recorded($dueTime, new OnErrorNotification($error), $comparer);
}

function onNext(int $dueTime, $value, callable $comparer = null)
{
    if ($value instanceof Observable) {
        try {
            $notification = new OnNextObservableNotification($value, FunctionalTestCase::getScheduler());
        } catch (Throwable $e) {
            $notification = new OnErrorNotification($e);
        }
    } else {
        $notification = new OnNextNotification($value);
    }
    return new Recorded($dueTime, $notification, $comparer);
}

function onCompleted(int $dueTime, callable $comparer = null)
{
    return new Recorded($dueTime, new OnCompletedNotification(), $comparer);
}

function subscribe(int $start, int $end = PHP_INT_MAX)
{
    return new Subscription($start, $end);
}

function RxIdentity($x)
{
    return $x;
}
