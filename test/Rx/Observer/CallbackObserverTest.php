<?php

declare(strict_types = 1);

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

    /**
     * @test
     */
    public function it_does_not_call_on_next_after_an_error()
    {
        $called = false;
        $record = function() use (&$called) { $called = true; };

        $observer = new CallbackObserver($record, function(){});
        $observer->onError(new Exception());

        $called = false;

        $observer->onNext(42);
        $this->assertFalse($called);
    }

    /**
     * @test
     */
    public function it_does_not_call_on_completed_after_an_error()
    {
        $called = false;
        $record = function() use (&$called) { $called = true; };

        $observer = new CallbackObserver(function(){}, function(){}, $record);
        $observer->onError(new Exception());

        $called = false;

        $observer->onCompleted();
        $this->assertFalse($called);
    }

    /**
     * @test
     */
    public function it_does_not_call_on_error_after_an_error()
    {
        $called = false;
        $record = function() use (&$called) { $called = true; };

        $observer = new CallbackObserver(function(){}, $record);
        $observer->onError(new Exception());

        $called = false;

        $observer->onError(new Exception());
        $this->assertFalse($called);
    }

    /**
     * @test
     */
    public function it_does_not_call_on_next_after_completion()
    {
        $called = false;
        $record = function() use (&$called) { $called = true; };

        $observer = new CallbackObserver($record, function(){});
        $observer->onCompleted();

        $called = false;

        $observer->onNext(42);
        $this->assertFalse($called);
    }

    /**
     * @test
     */
    public function it_does_not_call_on_completed_after_completion()
    {
        $called = false;
        $record = function() use (&$called) { $called = true; };

        $observer = new CallbackObserver(function(){}, function(){}, $record);
        $observer->onCompleted();

        $called = false;

        $observer->onCompleted();
        $this->assertFalse($called);
    }

    /**
     * @test
     */
    public function it_does_not_call_on_error_after_completion()
    {
        $called = false;
        $record = function() use (&$called) { $called = true; };

        $observer = new CallbackObserver(function(){}, $record);
        $observer->onCompleted();

        $called = false;

        $observer->onError(new Exception());
        $this->assertFalse($called);
    }
}

class TestException extends Exception {}
