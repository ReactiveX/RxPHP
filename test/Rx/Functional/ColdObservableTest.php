<?php

declare(strict_types = 1);

namespace Rx\Functional;

class ColdObservableTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_calls_relative_to_subscribe_time()
    {
        $xs = $this->createColdObservable(array(
            onNext(50, "foo"),
            onNext(75, "Bar"),
            onCompleted(105)
        ));

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs;
        });

        $this->assertCount(3, $results->getMessages());
        $this->assertMessages(array(
            onNext(250, "foo"),
            onNext(275, "Bar"),
            onCompleted(305)
        ), $results->getMessages());
    }
}
