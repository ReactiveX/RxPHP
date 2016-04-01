<?php

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Notification\OnCompletedNotification;
use Rx\Notification\OnNextNotification;
use Rx\Observable;

class MaterializeTest extends FunctionalTestCase
{
    public function testMaterializeNever()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return Observable::never()->materialize();
        });

        $this->assertMessages([], $results->getMessages());
    }

    public function testMaterializeEmpty()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->materialize();
        });

        $this->assertMessages([
            onNext(250, new OnNextNotification('123')),
            onCompleted(250)
        ], $results->getMessages());
    }
}
