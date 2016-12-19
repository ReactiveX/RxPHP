<?php

namespace Rx\Functional\React;

use Exception;
use Rx\Functional\FunctionalTestCase;
use Rx\React\Promise;
use Rx\React\PromiseFactory;
use Rx\React\RejectedPromiseException;

class PromiseFactoryTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function from_promise_success()
    {
        $source = PromiseFactory::toObservable(function() {
            return Promise::resolved(42);
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
            return Promise::rejected(42);
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
            return Promise::rejected($error);
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
