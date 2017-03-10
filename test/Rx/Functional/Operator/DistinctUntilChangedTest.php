<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Notification\OnCompletedNotification;
use Rx\Notification\OnNextNotification;
use Rx\Observable;
use Rx\Observable\EmptyObservable;
use Rx\Testing\Recorded;

class DistinctUntilChangedTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function distinct_until_changed_never()
    {

        $results = $this->scheduler->startWithCreate(function () {
            $o = new EmptyObservable($this->scheduler);

            return Observable::never()->distinctUntilChanged();
        });

        $this->assertMessages([], $results->getMessages());
    }


    /**
     * @test
     */
    public function distinct_until_changed_empty()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinctUntilChanged();
        });

        $messages = $results->getMessages();
        $this->assertEquals(1, count($messages));
        $this->assertTrue($messages[0] instanceof Recorded && $messages[0]->getValue() instanceof OnCompletedNotification);
        $this->assertEquals(250, $messages[0]->getTime());
    }

    /**
     * @test
     */
    public function distinct_until_changed_return()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(220, 2),
          onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinctUntilChanged();
        });

        $messages = $results->getMessages();
        $this->assertEquals(2, count($messages));
        $this->assertTrue($messages[0]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[0]->getValue() == new OnNextNotification(2));
        $this->assertEquals(220, $messages[0]->getTime());

        $this->assertTrue($messages[1]->getValue() instanceof OnCompletedNotification);
        $this->assertEquals(250, $messages[1]->getTime());
    }

    /**
     * @test
     */
    public function distinct_until_changed_throw()
    {
        $ex = new \Exception('ex');
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onError(250, $ex)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinctUntilChanged();
        });

        $this->assertMessages([onError(250, $ex)], $results->getMessages());
    }

    /**
     * @test
     */
    public function distinct_until_changed_all_changes()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(210, 2),
          onNext(220, 3),
          onNext(230, 4),
          onNext(240, 5),
          onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinctUntilChanged();
        });

        $messages = $results->getMessages();
        $this->assertEquals(5, count($messages));

        $this->assertTrue($messages[0]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[0]->getValue() == new OnNextNotification(2));
        $this->assertEquals(210, $messages[0]->getTime());

        $this->assertTrue($messages[1]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[1]->getValue() == new OnNextNotification(3));
        $this->assertEquals(220, $messages[1]->getTime());

        $this->assertTrue($messages[2]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[2]->getValue() == new OnNextNotification(4));
        $this->assertEquals(230, $messages[2]->getTime());

        $this->assertTrue($messages[3]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[3]->getValue() == new OnNextNotification(5));
        $this->assertEquals(240, $messages[3]->getTime());

        $this->assertTrue($messages[4]->getValue() instanceof OnCompletedNotification);
        $this->assertEquals(250, $messages[4]->getTime());
    }

    /**
     * @test
     */
    public function distinct_until_changed_all_same()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(210, 2),
          onNext(220, 2),
          onNext(230, 2),
          onNext(240, 2),
          onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinctUntilChanged();
        });

        $messages = $results->getMessages();
        $this->assertEquals(2, count($messages));

        $this->assertTrue($messages[0]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[0]->getValue() == new OnNextNotification(2));
        $this->assertEquals(210, $messages[0]->getTime());

        $this->assertTrue($messages[1]->getValue() instanceof OnCompletedNotification);
        $this->assertEquals(250, $messages[1]->getTime());
    }

    /**
     * @test
     */
    public function distinct_until_changed_some_changes()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(210, 2),
          onNext(215, 3),
          onNext(220, 3),
          onNext(225, 2),
          onNext(230, 2),
          onNext(230, 1),
          onNext(240, 2),
          onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinctUntilChanged();
        });

        $messages = $results->getMessages();
        $this->assertEquals(6, count($messages));

        $this->assertTrue($messages[0]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[0]->getValue() == new OnNextNotification(2));
        $this->assertEquals(210, $messages[0]->getTime());

        $this->assertTrue($messages[1]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[1]->getValue() == new OnNextNotification(3));
        $this->assertEquals(215, $messages[1]->getTime());

        $this->assertTrue($messages[2]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[2]->getValue() == new OnNextNotification(2));
        $this->assertEquals(225, $messages[2]->getTime());

        $this->assertTrue($messages[3]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[3]->getValue() == new OnNextNotification(1));
        $this->assertEquals(230, $messages[3]->getTime());


        $this->assertTrue($messages[4]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[4]->getValue() == new OnNextNotification(2));
        $this->assertEquals(240, $messages[4]->getTime());

        $this->assertTrue($messages[5]->getValue() instanceof OnCompletedNotification);
        $this->assertEquals(250, $messages[5]->getTime());
    }

    /**
     * @test
     */
    public function distinct_until_changed_comparer_all_different()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(210, 2),
          onNext(220, 3),
          onNext(230, 4),
          onNext(240, 5),
          onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinctUntilChanged();
        });

        $messages = $results->getMessages();
        $this->assertEquals(5, count($messages));

        $this->assertTrue($messages[0]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[0]->getValue() == new OnNextNotification(2));
        $this->assertEquals(210, $messages[0]->getTime());

        $this->assertTrue($messages[1]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[1]->getValue() == new OnNextNotification(3));
        $this->assertEquals(220, $messages[1]->getTime());

        $this->assertTrue($messages[2]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[2]->getValue() == new OnNextNotification(4));
        $this->assertEquals(230, $messages[2]->getTime());

        $this->assertTrue($messages[3]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[3]->getValue() == new OnNextNotification(5));
        $this->assertEquals(240, $messages[3]->getTime());

        $this->assertTrue($messages[4]->getValue() instanceof OnCompletedNotification);
        $this->assertEquals(250, $messages[4]->getTime());
    }

    /**
     * @test
     */
    public function distinct_until_changed_keyselector_div2()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(210, 2),
          onNext(220, 4),
          onNext(230, 3),
          onNext(240, 5),
          onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinctUntilKeyChanged(function ($x) {
                return $x % 2;
            });
        });

        $messages = $results->getMessages();
        $this->assertEquals(3, count($messages));

        $this->assertTrue($messages[0]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[0]->getValue() == new OnNextNotification(2));
        $this->assertEquals(210, $messages[0]->getTime());

        $this->assertTrue($messages[1]->getValue() instanceof OnNextNotification);
        $this->assertTrue($messages[1]->getValue() == new OnNextNotification(3));
        $this->assertEquals(230, $messages[1]->getTime());

        $this->assertTrue($messages[2]->getValue() instanceof OnCompletedNotification);
        $this->assertEquals(250, $messages[2]->getTime());
    }

    /**
     * @test
     */
    public function distinct_until_changed_key_selector_throws()
    {

        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(210, 2),
          onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinctUntilKeyChanged(function ($x) {
                throw new \Exception('ex');
            });
        });

        $this->assertMessages([onError(210, new \Exception('ex'))], $results->getMessages());
    }

    /**
     * @test
     */
    public function distinct_until_changed_comparer_throws()
    {
        $xs = $this->createHotObservable([
          onNext(150, 1),
          onNext(210, 2),
          onNext(220, 3),
          onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->distinctUntilChanged(function ($x, $y) {
                throw new \Exception('ex');
            });
        });

        $this->assertMessages([onNext(210, 2), onError(220, new \Exception('ex'))], $results->getMessages());
    }

}
