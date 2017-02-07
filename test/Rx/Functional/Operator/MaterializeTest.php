<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Notification;
use Rx\Notification\OnCompletedNotification;
use Rx\Notification\OnErrorNotification;
use Rx\Notification\OnNextNotification;
use Rx\Observable;

class MaterializeTest extends FunctionalTestCase
{
    private function getNotificationValue(Notification $notification)
    {
        if ($notification instanceof OnCompletedNotification) {
            throw new \Exception("Unable to get value of OnCompletedNotification");
        }

        $value = null;
        $valueGrabber = function ($v) use (&$value) {
            $value = $v;
        };

        $notification->accept($valueGrabber, $valueGrabber);
        
        return $value;
    }
    
    public function materializedNotificationsEqual(Notification $a, Notification $b)
    {
        $aValue = $this->getNotificationValue($a);
        $bValue = $this->getNotificationValue($b);
        
        if (get_class($aValue) !== get_class($bValue)) {
            return false;
        }
        
        if (!($aValue instanceof Notification)) {
            return false;
        }
        
        if ($aValue instanceof OnCompletedNotification) {
            return true;
        }
        
        return $this->getNotificationValue($aValue) === $this->getNotificationValue($bValue);
    }

    /**
     * @test
     */
    public function materialize_never()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return Observable::never()->materialize();
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function materialize_empty()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->materialize();
        });

        $this->assertMessages([
            onNext(250, new OnCompletedNotification(), [$this, 'materializedNotificationsEqual']),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function materialize_return()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->materialize();
        });

        $this->assertMessages([
            onNext(210, new OnNextNotification(2), [$this, 'materializedNotificationsEqual']),
            onNext(250, new OnCompletedNotification(), [$this, 'materializedNotificationsEqual']),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function materialize_throw()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onError(250, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->materialize();
        });

        $this->assertMessages([
            onNext(250, new OnErrorNotification($error), [$this, 'materializedNotificationsEqual']),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     * @requires function Rx\Observable::dematerialize
     */
    public function materialize_dematerialize_never()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return Observable::never()->materialize()->dematerialize();
        });

        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     * @requires function Rx\Observable::dematerialize
     */
    public function materialize_dematerialize_empty()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->materialize()->dematerialize();
        });

        $this->assertMessages([
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     * @requires function Rx\Observable::dematerialize
     */
    public function materialize_dematerialize_return()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->materialize()->dematerialize();
        });

        $this->assertMessages([
            onNext(210, 2),
            onCompleted(250)
        ], $results->getMessages());
    }

    /**
     * @test
     * @requires function Rx\Observable::dematerialize
     */
    public function materialize_dematerialize_throw()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onError(250, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->materialize()->dematerialize();
        });

        $this->assertMessages([
            onError(250, $error)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function materialize_dispose()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->materialize();
        }, 225);

        $this->assertMessages([
            onNext(210, new OnNextNotification(2), [$this, 'materializedNotificationsEqual']),
            onNext(220, new OnNextNotification(3), [$this, 'materializedNotificationsEqual']),
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function materialize_dematerialize_dispose()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->materialize()->dematerialize();
        }, 225);

        $this->assertMessages([
            onNext(210, 2),
            onNext(220, 3),
        ], $results->getMessages());
    }
}
