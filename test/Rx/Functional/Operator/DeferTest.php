<?php


namespace Rx\Functional\Operator;


use Rx\Functional\FunctionalTestCase;
use Rx\Observable\BaseObservable;


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
            return BaseObservable::defer(function () use (&$invoked, &$xs) {
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
            return BaseObservable::defer(function () use (&$invoked, &$xs) {
                $invoked++;
                $xs = $this->createColdObservable([
                  onNext(100, $this->scheduler->getClock()),
                  onError(200, new \Exception("error"))
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
            return BaseObservable::defer(function () use (&$invoked, &$xs) {
                $invoked++;
                $xs = $this->createColdObservable([
                  onNext(100, $this->scheduler->getClock()),
                  onNext(200, $invoked),
                  onNext(1100, 1000)
                ]);

                return $xs;
            });
        });


        $this->assertMessages([onNext(300, 200),onNext(400, 1)], $results->getMessages());

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
            return BaseObservable::defer(function () use (&$invoked) {
                $invoked++;
                throw new \Exception('error');
            });
        });

        $this->assertMessages([onError(200, new \Exception('error'))], $results->getMessages());

        $this->assertEquals(1, $invoked);

    }
}
