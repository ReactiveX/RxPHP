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
    public function promise_success()
    {
        $source = Observable::of(42);

        $promise = Promise::fromObservable($source);

        $promise->then(
          function ($value) {
              $this->assertEquals(42, $value);
          },
          function () {
              $this->assertTrue(false);
          });
    }

    /**
     * @test
     *
     */
    public function promise_failure()
    {
        $source = (new Subject());
        $source->onError(new Exception("some error"));

        $promise = Promise::fromObservable($source);

        $promise->then(
          function ($value) {
              $this->assertTrue(false);
          },
          function ($error) {
              $this->assertEquals($error, new Exception("some error"));
          });
    }
}
