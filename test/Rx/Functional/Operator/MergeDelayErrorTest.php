<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;

class MergeDelayErrorTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_waits_for_complete_before_emitting_error()
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
                                              onError(160, new \Exception()),
                                              onNext(200, 'qux'),
                                              onCompleted(250)
                                          ));

        $results = $this->scheduler->startWithCreate(function() use ($xs, $ys) {
            return $xs->mergeDelayError($ys);
        });

        $this->assertMessages(array(
                                  onNext(250, 'foo'),
                                  onNext(300, 4),
                                  onNext(300, 'bar'),
                                  onNext(350, 'baz'),
                                  onNext(400, 2),
                                  onNext(500, 3),
                                  onNext(600, 1),
                                  onError(700, new \Exception())
                              ), $results->getMessages());

        $this->assertSubscriptions(array(subscribe(200, 700)), $xs->getSubscriptions());
        $this->assertSubscriptions(array(subscribe(200, 360)), $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function it_waits_for_complete_before_emitting_error_2()
    {
        $xs = $this->createColdObservable(array(
                                              onNext(100, 4),
                                              onError(160, new \Exception()),
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
            return $xs->mergeDelayError($ys);
        });

        $this->assertMessages(array(
                                  onNext(250, 'foo'),
                                  onNext(300, 4),
                                  onNext(300, 'bar'),
                                  onNext(350, 'baz'),
                                  onNext(400, 'qux'),
                                  onError(450, new \Exception())
                              ), $results->getMessages());

        $this->assertSubscriptions(array(subscribe(200, 360)), $xs->getSubscriptions());
        $this->assertSubscriptions(array(subscribe(200, 450)), $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function it_works_when_both_sources_error()
    {
        $xs = $this->createColdObservable(array(
                                              onNext(100, 4),
                                              onError(160, new \Exception()),
                                              onNext(200, 2),
                                              onNext(300, 3),
                                              onNext(400, 1),
                                              onCompleted(500)
                                          ));

        $ys = $this->createColdObservable(array(
                                              onNext(50, 'foo'),
                                              onNext(100, 'bar'),
                                              onNext(150, 'baz'),
                                              onError(161, new \Exception()),
                                              onNext(200, 'qux'),
                                              onCompleted(250)
                                          ));

        $results = $this->scheduler->startWithCreate(function() use ($xs, $ys) {
            return $xs->mergeDelayError($ys);
        });

        $this->assertMessages(array(
                                  onNext(250, 'foo'),
                                  onNext(300, 4),
                                  onNext(300, 'bar'),
                                  onNext(350, 'baz'),
                                  onError(361, new \Exception())
                              ), $results->getMessages());

        $this->assertSubscriptions(array(subscribe(200, 360)), $xs->getSubscriptions());
        $this->assertSubscriptions(array(subscribe(200, 361)), $ys->getSubscriptions());
    }
}