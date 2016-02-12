<?php


namespace Rx\Functional\Operator;

use Exception;
use React\EventLoop\Factory;
use Rx\Disposable\CallbackDisposable;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable\AnonymousObservable;
use Rx\Observable;
use Rx\Observable\EmptyObservable;
use Rx\Observer\CallbackObserver;
use Rx\Scheduler\EventLoopScheduler;

class AsObservableTest extends FunctionalTestCase
{
    public function testAsObservableHides()
    {
        $someObservable = new EmptyObservable();
        return ($someObservable->asObservable() != $someObservable);
    }

    public function testAsObservableNever()
    {

        $results = $this->scheduler->startWithCreate(function () {
            return Observable::never()->asObservable();
        });

        $this->assertMessages(
            [],
            $results->getMessages()
        );
    }

    public function testAsObservableEmpty()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->asObservable();
        });

        $this->assertMessages(
            [
                onCompleted(250)
            ],
            $results->getMessages()
        );
    }

    public function testAsObservableThrow()
    {
        $error = new Exception();

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(250, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->asObservable();
        });

        $this->assertMessages(
            [
                onError(250, $error)
            ],
            $results->getMessages()
        );
    }

    public function testAsObservableJust()
    {

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(220, 2),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->asObservable();
        });

        $this->assertMessages(
            [
                onNext(220, 2),
                onCompleted(250)
            ],
            $results->getMessages()
        );
    }

    public function testAsObservableIsNotEager()
    {

        $subscribed = false;

        $xs = new AnonymousObservable(function ($obs) use (&$subscribed) {
            $subscribed = true;
            $disp       = $this->createHotObservable(
                [
                    onNext(150, 1),
                    onNext(220, 2),
                    onCompleted(250)
                ]

            )->subscribe($obs);

            return new CallbackDisposable(function () use ($disp) {
                return $disp->dispose();
            });
        });
        $xs->asObservable();

        $this->assertTrue(!$subscribed);

        $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->asObservable();
        });

        $this->assertTrue($subscribed);
    }

    public function testAsObservablePassThroughScheduler()
    {
        $loop      = Factory::create();
        $scheduler = new EventLoopScheduler($loop);

        $gotValue = false;
        Observable::interval(10 /*will throw if it doesn't get passed the event loop scheduler*/)
            ->asObservable()
            ->take(1)
            ->subscribe(new CallbackObserver(
                function ($x) use (&$gotValue) {
                    $this->assertEquals(0, $x);
                    $gotValue = true;
                }
            ), $scheduler);

        $loop->run();

        $this->assertTrue($gotValue);
    }
}
