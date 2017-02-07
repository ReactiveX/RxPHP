<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Exception;
use Rx\Functional\FunctionalTestCase;


class SelectManyTest extends FunctionalTestCase
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
            return $xs->selectMany(function() use ($ys) { return $ys; });
        });

        $this->assertMessages(array(
            onNext(350, "foo"), // 1
            onNext(400, "bar"), // 1
            onNext(450, "baz"), // 1
            onNext(450, "foo"), // 2
            onNext(500, "qux"), // 1
            onNext(500, "bar"), // 2
            onNext(550, "baz"), // 2
            onNext(550, "foo"), // 3
            onNext(600, "qux"), // 2
            onNext(600, "bar"), // 3
            onNext(650, "baz"), // 3
            onNext(650, "foo"), // 4
            onNext(700, "qux"), // 3
            onNext(700, "bar"), // 4
            onNext(750, "baz"), // 4
            onNext(800, "qux"), // 4
            onCompleted(850)
        ), $results->getMessages());

        $this->assertSubscriptions(array(subscribe(200, 700)), $xs->getSubscriptions());
        $this->assertSubscriptions(array(subscribe(300, 550), subscribe(400, 650), subscribe(500, 750), subscribe(600, 850)), $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function it_passes_on_error()
    {
        $xs = $this->createColdObservable(array(
            onNext(100, 4),
            onNext(200, 2),
            onNext(300, 3),
            onNext(400, 1),
            onCompleted(510)
        ));

        $ys = $this->createColdObservable(array(
            onNext(50, 'foo'),
            onNext(100, 'bar'),
            onNext(150, 'baz'),
            onError(210, new Exception()),
            onCompleted(250),
        ));

        $results = $this->scheduler->startWithCreate(function() use ($xs, $ys) {
            return $xs->selectMany(function() use ($ys) { return $ys; });
        });

        $this->assertMessages(array(
            onNext(350, "foo"), // 1
            onNext(400, "bar"), // 1
            onNext(450, "baz"), // 1
            onNext(450, "foo"), // 2
            onNext(500, "bar"), // 2
            onError(510, new Exception()), // 1
        ), $results->getMessages());

        $this->assertSubscriptions(array(subscribe(200, 510)), $xs->getSubscriptions());
        $this->assertSubscriptions(array(subscribe(300, 510), subscribe(400, 510), subscribe(500, 510)), $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function flatMapTo_it_passes_the_last_on_complete()
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
            return $xs->flatMapTo($ys);
        });

        $this->assertMessages(array(
            onNext(350, "foo"), // 1
            onNext(400, "bar"), // 1
            onNext(450, "baz"), // 1
            onNext(450, "foo"), // 2
            onNext(500, "qux"), // 1
            onNext(500, "bar"), // 2
            onNext(550, "baz"), // 2
            onNext(550, "foo"), // 3
            onNext(600, "qux"), // 2
            onNext(600, "bar"), // 3
            onNext(650, "baz"), // 3
            onNext(650, "foo"), // 4
            onNext(700, "qux"), // 3
            onNext(700, "bar"), // 4
            onNext(750, "baz"), // 4
            onNext(800, "qux"), // 4
            onCompleted(850)
        ), $results->getMessages());

        $this->assertSubscriptions(array(subscribe(200, 700)), $xs->getSubscriptions());
        $this->assertSubscriptions(array(subscribe(300, 550), subscribe(400, 650), subscribe(500, 750), subscribe(600, 850)), $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function flatMapTo_it_passes_on_error()
    {
        $xs = $this->createColdObservable(array(
            onNext(100, 4),
            onNext(200, 2),
            onNext(300, 3),
            onNext(400, 1),
            onCompleted(510)
        ));

        $ys = $this->createColdObservable(array(
            onNext(50, 'foo'),
            onNext(100, 'bar'),
            onNext(150, 'baz'),
            onError(210, new Exception()),
            onCompleted(250),
        ));

        $results = $this->scheduler->startWithCreate(function() use ($xs, $ys) {
            return $xs->flatMapTo($ys);
        });

        $this->assertMessages(array(
            onNext(350, "foo"), // 1
            onNext(400, "bar"), // 1
            onNext(450, "baz"), // 1
            onNext(450, "foo"), // 2
            onNext(500, "bar"), // 2
            onError(510, new Exception()), // 1
        ), $results->getMessages());

        $this->assertSubscriptions(array(subscribe(200, 510)), $xs->getSubscriptions());
        $this->assertSubscriptions(array(subscribe(300, 510), subscribe(400, 510), subscribe(500, 510)), $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function flatMap_it_errors_with_bad_return()
    {
        $xs = $this->createColdObservable([
            onNext(100, 4),
            onNext(200, 2),
            onNext(300, 3),
            onNext(400, 1),
            onCompleted(510)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->flatMap(function () {
                return 'unexpected string';
            });
        });

        $this->assertMessages([
            onError(300, new Exception()),
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 300)], $xs->getSubscriptions());
    }
}
