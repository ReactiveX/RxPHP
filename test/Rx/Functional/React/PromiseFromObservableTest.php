<?php


namespace Rx\Functional\React;

use Exception;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\React\Promise;
use Rx\Subject\Subject;

class PromiseFromObservableTest extends FunctionalTestCase
{
    /**
     * @test
     *
     */
    public function promise_success(): void
    {
        $source = Observable::of(42);

        $promise = Promise::fromObservable($source);

        $promise->then(
          function ($value): void {
              $this->assertEquals(42, $value);
          },
          function (): void {
              $this->assertTrue(false);
          });
    }

    /**
     * @test
     *
     */
    public function promise_failure(): void
    {
        $source = (new Subject());
        $source->onError(new Exception("some error"));

        $promise = Promise::fromObservable($source);

        $promise->then(
          function ($value): void {
              $this->assertTrue(false);
          },
          function ($error): void {
              $this->assertEquals($error, new Exception("some error"));
          });
    }
}
