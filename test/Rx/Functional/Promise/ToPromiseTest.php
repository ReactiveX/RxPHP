<?php

namespace Rx\Functional\Promise;

use Exception;
use Interop\Async\Loop;
use Interop\Async\Loop\Test\DummyDriver;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Promise\Promise;

class ToPromiseTest extends FunctionalTestCase
{
    /**
     * @test
     *
     */
    public function promise_success()
    {
        $promise = Observable::of(42)->toPromise();

        $promise->when(function (Exception $ex = null, $value) {
            $this->assertEquals(null, $ex);
            $this->assertEquals(42, $value);
        });
    }

    /**
     * @test
     *
     */
    public function promise_failure()
    {
        $promise = Observable::error(new Exception('some error'))->toPromise();

        $promise->when(function (Exception $ex = null, $value) {
            $this->assertEquals(new Exception('some error'), $ex);
            $this->assertEquals(null, $value);
        });
    }

    /**
     * @test
     *
     */
    public function promise_error_handler()
    {
        $driver  = new DummyDriver();
        $factory = $this->getMockBuilder(Loop\DriverFactory::class)->getMock();
        $factory->method('create')->willReturn($driver);
        Loop::setFactory($factory);

        Loop::setErrorHandler(function (\Exception $e) use (&$thrownError) {
            $thrownError = $e;
        });

        $promise = Observable::of(42)->toPromise();

        $promise->when(function (Exception $ex = null, $value) {
            throw new Exception('error');
        });

        Loop::get()->run();

        $this->assertEquals(new Exception('error'), $thrownError);
    }

    /**
     * @test
     *
     */
    public function promise_within_promise_success()
    {
        $promise1 = new Promise(Observable::of(42));

        $promise2 = Observable::of($promise1)->toPromise();

        $promise2->when(function (Exception $ex = null, $value) {
            $this->assertEquals(null, $ex);
            $this->assertEquals(42, $value);
        });
    }

    /**
     * @test
     *
     */
    public function promise_within_promise_failure()
    {
        $promise1 = new Promise(Observable::error(new Exception('some error')));

        $promise2 = Observable::of($promise1)->toPromise();

        $promise2->when(function (Exception $ex = null, $value) {
            $this->assertEquals(new Exception('some error'), $ex);
            $this->assertEquals(null, $value);
        });
    }
}
