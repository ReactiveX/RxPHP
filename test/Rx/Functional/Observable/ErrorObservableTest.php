<?php

declare(strict_types = 1);

namespace Rx\Functional\Observable;

use Rx\Functional\FunctionalTestCase;
use Rx\Notification\OnErrorNotification;
use Rx\Observable\ErrorObservable;
use Rx\Scheduler\ImmediateScheduler;

class ErrorObservableTest extends FunctionalTestCase
{
    public function testErrorObservableWillAcceptThrowable()
    {
        $throwable = null;

        try {
            // Generate ArithmeticError
            1 << -1;
        } catch (\Throwable $e) {
            $throwable = $e;
            $errorObservable = new ErrorObservable($e, $this->scheduler);
        }

        $result = $this->scheduler->startWithCreate(function () use ($errorObservable) {
            return $errorObservable;
        });

        $this->assertMessages([
            // This does not do strict checking by defulat - that
            // is why a comparer is passed in
            onError(201, $throwable, function (OnErrorNotification $a, OnErrorNotification $b) {
                return $a->__toString() === $b->__toString();
            })
        ], $result->getMessages());
    }
}