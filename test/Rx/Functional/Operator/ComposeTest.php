<?php

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;

class ComposeTest extends FunctionalTestCase
{
    public function testSimpleCompose()
    {
        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(201, 2),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->compose(function ($observable) {
                return $observable->map(function ($x) {
                    return $x + 1;
                });
            });
        });

        $this->assertMessages(
            [
                onNext(201, 3),
                onCompleted(250)
            ],
            $results->getMessages()
        );
    }
}
