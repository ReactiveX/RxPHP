<?php

namespace Rx\Functional\Promise;

use Exception;
use Rx\Functional\FunctionalTestCase;
use Rx\Promise\Promise;
use Rx\Promise\PromiseFactory;
use Rx\Promise\RejectedPromiseException;
use function React\Promise\resolve;
use function React\Promise\reject;

class PromiseFactoryTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function from_promise_success()
    {
        $source = PromiseFactory::toObservable(function() {
            return resolve(42);
        });

        $results = $this->scheduler->startWithCreate(function() use ($source) {
            return $source;
        });

        $this->assertMessages(array(
            onNext(200, 42),
            onCompleted(200),
        ), $results->getMessages());
    }

    /**
     * @test
     */
    public function from_promise_reject_non_exception()
    {
        $source = PromiseFactory::toObservable(function () {
            return reject(42);
        });

        $theException = null;

        $source->subscribe(
            [$this, 'fail'],
            function ($err) use (&$theException) {
                $theException = $err;
            },
            [$this, 'fail'],
            $this->scheduler
        );

        $this->scheduler->start();

        $this->assertTrue($theException instanceof RejectedPromiseException);
        $this->assertEquals(42, $theException->getRejectValue());
    }

    /**
     * @test
     */
    public function from_promise_reject()
    {
        $error = new Exception("Test exception");

        $source = PromiseFactory::toObservable(function () use ($error) {
            return reject($error);
        });

        $theException = null;

        $source->subscribe(
            [$this, 'fail'],
            function ($err) use (&$theException) {
                $theException = $err;
            },
            [$this, 'fail'],
            $this->scheduler
        );

        $this->scheduler->start();

        $this->assertTrue($theException instanceof Exception);
        $this->assertSame($error, $theException);
    }
}
