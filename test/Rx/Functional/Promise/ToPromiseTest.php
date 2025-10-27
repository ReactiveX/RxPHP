<?php

declare(strict_types=1);

namespace Rx\Functional\Promise;

use Exception;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class ToPromiseTest extends FunctionalTestCase
{
    /**
     * @test
     *
     */
    public function promise_success(): void
    {
        $promise = Observable::of(42)->toPromise();

        $result = null;

        $promise->then(function ($value) use (&$result): void {
            $result = $value;
        });

        $this->assertEquals(42, $result);
    }

    /**
     * @test
     *
     */
    public function promise_failure(): void
    {
        $promise = Observable::error(new Exception('some error'))->toPromise();

        $error = null;

        $promise->then(
            function (): void {
            },
            function ($ex) use (&$error): void {
                $error = $ex;
            });

        $this->assertEquals(new Exception('some error'), $error);
    }

    /**
     * @test
     *
     */
    public function promise_within_promise_success(): void
    {
        $promise1 = \React\Promise\resolve(42);

        $promise2 = Observable::of($promise1)->toPromise();

        $result = null;

        $promise2->then(function ($value) use (&$result): void {
            $result = $value;
        });

        $this->assertEquals(42, $result);
    }

    /**
     * @test
     *
     */
    public function promise_within_promise_failure(): void
    {
        $promise1 = \React\Promise\reject(new Exception('some error'));

        $promise2 = Observable::of($promise1)->toPromise();

        $error = null;

        $promise2->then(
            function (): void {
            },
            function (Exception $ex) use (&$error): void {
                $error = $ex;
            });

        $this->assertEquals(new Exception('some error'), $error);
    }

    /**
     * @test
     *
     */
    public function promise_cancel(): void
    {
        $disposed = false;

        $promise = Observable::timer(1000)
            ->mapTo(42)
            ->finally(function () use (&$disposed): void {
                $disposed = true;
            })
            ->toPromise();

        $result = null;

        $promise->cancel();

        $promise->then(function ($value) use (&$result): void {
            $result = $value;
        });

        $this->assertEquals(null, $result);
        $this->assertTrue($disposed);
    }
}
