<?php

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;

class SwitchFirstTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function switchFirst_Data()
    {

        $xs = $this->createHotObservable([
            onNext(300, $this->createColdObservable([
                onNext(10, 101),
                onNext(20, 102),
                onNext(110, 103),
                onNext(120, 104),
                onNext(210, 105),
                onNext(220, 106),
                onCompleted(230)
            ])),
            onNext(400, $this->createColdObservable([
                onNext(10, 201),
                onNext(20, 202),
                onNext(30, 203),
                onNext(40, 204),
                onCompleted(50)
            ])),
            onNext(500, $this->createColdObservable([
                onNext(10, 301),
                onNext(20, 302),
                onNext(30, 303),
                onNext(40, 304),
                onCompleted(150)
            ])),
            onCompleted(600)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->switchFirst();
        });

        $this->assertMessages(
            [
                onNext(310, 101),
                onNext(320, 102),
                onNext(410, 103),
                onNext(420, 104),
                onNext(510, 105),
                onNext(520, 106),
                onCompleted(600)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function switchFirst_inner_throws()
    {

        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(300, $this->createColdObservable([
                onNext(10, 101),
                onNext(20, 102),
                onNext(110, 103),
                onNext(120, 104),
                onNext(210, 105),
                onNext(220, 106),
                onCompleted(230)
            ])),
            onNext(400, $this->createColdObservable([
                onNext(10, 201),
                onNext(20, 202),
                onNext(30, 203),
                onNext(40, 204),
                onError(50, $error)
            ])),
            onNext(500, $this->createColdObservable([
                onNext(10, 301),
                onNext(20, 302),
                onNext(30, 303),
                onNext(40, 304),
                onCompleted(150)
            ])),
            onCompleted(600)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->switchFirst();
        });

        $this->assertMessages(
            [
                onNext(310, 101),
                onNext(320, 102),
                onNext(410, 103),
                onNext(420, 104),
                onNext(510, 105),
                onNext(520, 106),
                onCompleted(600)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function switchFirst_outer_throws()
    {

        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(300, $this->createColdObservable([
                onNext(10, 101),
                onNext(20, 102),
                onNext(110, 103),
                onNext(120, 104),
                onNext(210, 105),
                onNext(220, 106),
                onCompleted(230)
            ])),
            onNext(400, $this->createColdObservable([
                onNext(10, 201),
                onNext(20, 202),
                onNext(30, 203),
                onNext(40, 204),
                onCompleted(50)
            ])),
            onError(500, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->switchFirst();
        });

        $this->assertMessages(
            [
                onNext(310, 101),
                onNext(320, 102),
                onNext(410, 103),
                onNext(420, 104),
                onError(500, $error)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function switchFirst_no_inner()
    {

        $xs = $this->createHotObservable([
            onCompleted(500)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->switchFirst();
        });

        $this->assertMessages(
            [
                onCompleted(500)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function switchFirst_inner_completes()
    {

        $xs = $this->createHotObservable([
            onNext(300, $this->createColdObservable([
                onNext(10, 101),
                onNext(20, 102),
                onNext(110, 103),
                onNext(120, 104),
                onNext(210, 105),
                onNext(220, 106),
                onCompleted(230)
            ])),

            onCompleted(540)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->switchFirst();
        });

        $this->assertMessages(
            [
                onNext(310, 101),
                onNext(320, 102),
                onNext(410, 103),
                onNext(420, 104),
                onNext(510, 105),
                onNext(520, 106),
                onCompleted(540)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function switchFirst_Dispose()
    {

        $xs = $this->createHotObservable([
            onNext(300, $this->createColdObservable([
                onNext(10, 101),
                onNext(20, 102),
                onNext(110, 103),
                onNext(120, 104),
                onNext(210, 105),
                onNext(220, 106),
                onCompleted(230)
            ])),
            onNext(400, $this->createColdObservable([
                onNext(10, 201),
                onNext(20, 202),
                onNext(30, 203),
                onNext(40, 204),
                onCompleted(50)
            ])),
            onNext(500, $this->createColdObservable([
                onNext(10, 301),
                onNext(20, 302),
                onNext(30, 303),
                onNext(40, 304),
                onCompleted(150)
            ])),
            onCompleted(600)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->switchFirst();
        }, 500);

        $this->assertMessages(
            [
                onNext(310, 101),
                onNext(320, 102),
                onNext(410, 103),
                onNext(420, 104)
            ],
            $results->getMessages()
        );
    }
}
