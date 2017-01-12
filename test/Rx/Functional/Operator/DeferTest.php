<?php

declare(strict_types = 1);


namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler\ImmediateScheduler;

class DeferTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function defer_complete()
    {
        $invoked = 0;
        $xs      = null;

        $results = $this->scheduler->startWithCreate(function () use (&$invoked, &$xs) {
            return Observable::defer(function () use (&$invoked, &$xs) {
                $invoked++;
                $xs = $this->createColdObservable([
                    onNext(100, $this->scheduler->getClock()),
                    onCompleted(200)
                ]);

                return $xs;
            });
        });


        $this->assertMessages([onNext(300, 200), onCompleted(400)], $results->getMessages());

        $this->assertEquals(1, $invoked);

        $this->assertSubscriptions([subscribe(200, 400)], $xs->getSubscriptions());

    }

    /**
     * @test
     */
    public function defer_error()
    {
        $invoked = 0;
        $xs      = null;

        $results = $this->scheduler->startWithCreate(function () use (&$invoked, &$xs) {
            return Observable::defer(function () use (&$invoked, &$xs) {
                $invoked++;
                $xs = $this->createColdObservable([
                    onNext(100, $this->scheduler->getClock()),
                    onError(200, new \Exception('error'))
                ]);

                return $xs;
            });
        });


        $this->assertMessages([onNext(300, 200), onError(400, new \Exception('error'))], $results->getMessages());

        $this->assertEquals(1, $invoked);

        $this->assertSubscriptions([subscribe(200, 400)], $xs->getSubscriptions());

    }

    /**
     * @test
     */
    public function defer_dispose()
    {
        $invoked = 0;
        $xs      = null;

        $results = $this->scheduler->startWithCreate(function () use (&$invoked, &$xs) {
            return Observable::defer(function () use (&$invoked, &$xs) {
                $invoked++;
                $xs = $this->createColdObservable([
                    onNext(100, $this->scheduler->getClock()),
                    onNext(200, $invoked),
                    onNext(1100, 1000)
                ]);

                return $xs;
            });
        });


        $this->assertMessages([onNext(300, 200), onNext(400, 1)], $results->getMessages());

        $this->assertEquals(1, $invoked);

        $this->assertSubscriptions([subscribe(200, 1000)], $xs->getSubscriptions());

    }

    /**
     * @test
     */
    public function defer_throw()
    {
        $invoked = 0;

        $results = $this->scheduler->startWithCreate(function () use (&$invoked) {
            return Observable::defer(function () use (&$invoked) {
                $invoked++;
                throw new \Exception('error');
            });
        });

        // Note: these tests differ from the RxJS tests that they were based on because RxJS was
        // explicitly using the immediate scheduler on subscribe internally. When we pass the
        // proper scheduler in, the subscription gets scheduled which requires an extra tick.
        $this->assertMessages([
            onError(200, new \Exception('error'))
        ], $results->getMessages());

        $this->assertEquals(1, $invoked);

    }

    /**
     * @test
     */
    public function defer_factory_returns_invalid_string()
    {
        $invoked = 0;

        $results = $this->scheduler->startWithCreate(function () use (&$invoked) {
            return Observable::defer(function () use (&$invoked) {
                $invoked++;
                return 'unexpected string';
            });
        });

        $this->assertMessages([
            onError(200, new \Exception())
        ], $results->getMessages());

        $this->assertEquals(1, $invoked);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage I take exception
     */
    public function defer_error_while_subscribe_with_immediate_scheduler()
    {
        Observable::defer(function () {
            return Observable::create(function (ObserverInterface $observer) {
                $observer->onError(new \Exception('I take exception'));
            });
        }, new ImmediateScheduler())->subscribe();
    }

    /**
     * @test
     */
    public function defer_error_while_subscribe_with_immediate_scheduler_passes_through()
    {
        $onErrorCalled = false;

        Observable::defer(function () {
            return Observable::create(function (ObserverInterface $observer) {
                $observer->onError(new \Exception('I take exception'));
            });
        }, new ImmediateScheduler())->subscribe(null, function (\Exception $e) use (&$onErrorCalled) {
            $onErrorCalled = true;
            $this->assertEquals('I take exception', $e->getMessage());
        });

        $this->assertTrue($onErrorCalled);
    }
}
