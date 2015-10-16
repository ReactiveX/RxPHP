<?php


namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable\AnonymousObservable;
use Rx\Observable\BaseObservable;
use Rx\Observable\EmptyObservable;

class ConcatTest extends FunctionalTestCase
{
    public function testConcatEmptyEmpty()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onCompleted(250)], $results->getMessages());
    }

    public function testConcatEmptyNever()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230)
        ]);
        $e2 = BaseObservable::never();
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([], $results->getMessages());
    }

    public function testConcatNeverEmpty()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230)
        ]);
        $e2 = BaseObservable::never();
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->concat($e1);
        });
        $this->assertMessages([], $results->getMessages());
    }

    public function testConcatNeverNever()
    {
        $e1 = BaseObservable::never();
        $e2 = BaseObservable::never();
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([], $results->getMessages());
    }

    public function testConcatEmptyThrow()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onError(250, new \Exception('error'))
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onError(250, new \Exception('error'))], $results->getMessages());
    }

    public function testConcatThrowEmpty()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onError(230, new \Exception('error'))
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onError(230, new \Exception('error'))], $results->getMessages());
    }

    public function testConcatThrowThrow()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onError(230, new \ErrorException())
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onError(250, new \Exception())
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onError(230, new \ErrorException())], $results->getMessages());
    }

    public function testConcatReturnEmpty()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(230)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(250)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onNext(210, 2), onCompleted(250)], $results->getMessages());
    }

    public function testConcatEmptyReturn()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(240, 2),
            onCompleted(250)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onNext(240, 2), onCompleted(250)], $results->getMessages());
    }

    public function testConcatReturnNever()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(230)
        ]);
        $e2 = BaseObservable::never();
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onNext(210, 2)], $results->getMessages());
    }

    public function testConcatNeverReturn()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(230)
        ]);
        $e2 = BaseObservable::never();
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e2->concat($e1);
        });
        $this->assertMessages([], $results->getMessages());
    }

    public function testConcatReturnReturn()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(220, 2),
            onCompleted(230)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(240, 3),
            onCompleted(250)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onNext(220, 2), onNext(240, 3), onCompleted(250)], $results->getMessages());
    }

    public function testConcatThrowReturn()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onError(220, new \Exception())
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(240, 3),
            onCompleted(250)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onError(220, new \Exception())], $results->getMessages());
    }

    public function testConcatReturnThrow()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(220, 2),
            onCompleted(230)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onError(250, new \Exception())
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([onNext(220, 2), onError(250, new \Exception())], $results->getMessages());
    }

    public function testConcatSomeDataOnBothSides()
    {
        $e1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onNext(220, 3),
            onCompleted(225)
        ]);
        $e2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($e1, $e2) {
            return $e1->concat($e2);
        });
        $this->assertMessages([
            onNext(210, 2),
            onNext(220, 3),
            onNext(230, 4),
            onNext(240, 5),
            onCompleted(250)
        ],
            $results->getMessages());
    }

    public function testConcatAsArguments()
    {
        $xs1 = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onNext(30, 3),
            onCompleted(40)
        ]);
        $xs2 = $this->createColdObservable([
            onNext(10, 4),
            onNext(20, 5),
            onCompleted(30)
        ]);
        $xs3 = $this->createColdObservable([
            onNext(10, 6),
            onNext(20, 7),
            onNext(30, 8),
            onNext(40, 9),
            onCompleted(50)
        ]);
        $results = $this->scheduler->startWithCreate(function () use ($xs1, $xs2, $xs3) {
            return (new EmptyObservable())->concat($xs1)->concat($xs2)->concat($xs3);
        });
        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
            onNext(230, 3),
            onNext(250, 4),
            onNext(260, 5),
            onNext(280, 6),
            onNext(290, 7),
            onNext(300, 8),
            onNext(310, 9),
            onCompleted(320)
        ],
            $results->getMessages());
        $this->assertSubscriptions([subscribe(200, 240)], $xs1->getSubscriptions());
        $this->assertSubscriptions([subscribe(240, 270)], $xs2->getSubscriptions());
        $this->assertSubscriptions([subscribe(270, 320)], $xs3->getSubscriptions());

    }
}