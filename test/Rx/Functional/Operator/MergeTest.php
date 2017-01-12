<?php

declare(strict_types = 1);

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
            onNext(250, 'foo'),
            onNext(300, 4),
            onNext(300, 'bar'),
            onNext(350, 'baz'),
            onNext(400, 2),
            onNext(400, 'qux'),
            onNext(500, 3),
            onNext(600, 1),
            onCompleted(700)
        ), $results->getMessages());

        $this->assertSubscriptions(array(subscribe(200, 700)), $xs->getSubscriptions());
        $this->assertSubscriptions(array(subscribe(200, 450)), $ys->getSubscriptions());
    }
}
