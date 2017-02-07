<?php

declare(strict_types = 1);

namespace Rx\Observer;

use Exception;
use Rx\Disposable\CallbackDisposable;
use Rx\TestCase;

class AutoDetachObserverTest extends TestCase
{
    /**
     * @test
     */
    public function it_calls_dispose_on_completed()
    {
        $disposed   = false;
        $disposable = new CallbackDisposable(function() use (&$disposed){ $disposed = true; });

        $observer   = new AutoDetachObserver(new CallbackObserver());
        $observer->setDisposable($disposable);

        $observer->onCompleted();
        $this->assertTrue($disposed);
    }

    /**
     * @test
     */
    public function it_calls_dispose_on_error()
    {
        $disposed   = false;
        $disposable = new CallbackDisposable(function() use (&$disposed){ $disposed = true; });

        $observer   = new AutoDetachObserver(new CallbackObserver(null, function(){}));
        $observer->setDisposable($disposable);

        $observer->onError(new Exception());
        $this->assertTrue($disposed);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage fail
     */
    public function it_disposes_if_observer_on_completed_throws()
    {
        $disposed   = false;
        $disposable = new CallbackDisposable(function() use (&$disposed){ $disposed = true; });

        $observer   = new AutoDetachObserver(new CallbackObserver(null, null, function() { throw new Exception('fail'); }));
        $observer->setDisposable($disposable);

        $observer->onCompleted();
        $this->assertTrue($disposed);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage fail
     */
    public function it_disposes_if_observer_on_error_throws()
    {
        $disposed   = false;
        $disposable = new CallbackDisposable(function() use (&$disposed){ $disposed = true; });

        $observer   = new AutoDetachObserver(new CallbackObserver(null, function() { throw new Exception('fail'); }));
        $observer->setDisposable($disposable);

        $observer->onError(new Exception());
        $this->assertTrue($disposed);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage fail
     */
    public function it_disposes_if_observer_on_next_throws()
    {
        $disposed   = false;
        $disposable = new CallbackDisposable(function() use (&$disposed){ $disposed = true; });

        $observer   = new AutoDetachObserver(new CallbackObserver(function() { throw new Exception('fail'); }));
        $observer->setDisposable($disposable);

        $observer->onNext(42);
        $this->assertTrue($disposed);
    }
}
