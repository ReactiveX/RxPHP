<?php

namespace Rx\Observer;

use Exception;
use Rx\TestCase;

class CallbackObserverTest extends TestCase
{
    /**
     * @test
     */
    public function it_calls_the_given_callable_on_next()
    {
        $called = false;
        $observer = new CallbackObserver(function() use (&$called) { $called = true; });

        $observer->onNext(42);
        $this->assertTrue($called);
    }

    /**
     * @test
     */
    public function it_calls_the_given_callable_on_error()
    {
        $called = false;
        $observer = new CallbackObserver(function(){}, function() use (&$called) { $called = true; });

        $observer->onError(new Exception());
        $this->assertTrue($called);
    }

    /**
     * @test
     */
    public function it_calls_the_given_callable_on_complete()
    {
        $called = false;
        $observer = new CallbackObserver(function(){}, function(){}, function() use (&$called) { $called = true; });

        $observer->onCompleted();
        $this->assertTrue($called);
    }

    /**
     * @test
     * @expectedException Rx\Observer\TestException
     */
    public function default_on_error_callable_rethrows_exception()
    {
        $observer = new CallbackObserver();

        $observer->onError(new TestException());
    }

    /**
     * @test
     */
    public function it_calls_on_next_with_the_given_value()
    {
        $called = null;
        $observer = new CallbackObserver(function($value) use (&$called) { $called = $value; });

        $observer->onNext(42);

        $this->assertEquals(42, $called);
    }
}

class TestException extends Exception {}
