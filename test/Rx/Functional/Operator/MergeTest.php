<?php

namespace Rx\Functional\Operator;


use Rx\Functional\FunctionalTestCase;

class MergeTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_passes_the_last_on_complete()
    {
        $xs = $this->createColdObservable(array(
            onNext(100, 4),
            onNext(200, 2),
            onNext(300, 3),
            onNext(400, 1),
            onCompleted(500)
        ));

        $ys = $this->createColdObservable(array(
            onNext(50, 'foo'),
            onNext(100, 'bar'),
            onNext(150, 'baz'),
            onNext(200, 'qux'),
            onCompleted(250)
        ));

        $results = $this->scheduler->startWithCreate(function() use ($xs, $ys) {
            return $xs->merge($ys);
        });

        $this->assertMessages(array(
            onNext(252, 'foo'),
            onNext(301, 4),
            onNext(302, 'bar'),
            onNext(352, 'baz'),
            onNext(401, 2),
            onNext(402, 'qux'),
            onNext(501, 3),
            onNext(601, 1),
            onCompleted(701)
        ), $results->getMessages());

        $this->assertSubscriptions(array(subscribe(201, 701)), $xs->getSubscriptions());
        $this->assertSubscriptions(array(subscribe(202, 452)), $ys->getSubscriptions());
    }
}
