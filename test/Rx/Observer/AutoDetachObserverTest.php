<?php

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
}
