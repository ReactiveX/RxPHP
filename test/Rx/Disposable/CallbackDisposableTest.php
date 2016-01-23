<?php

namespace Rx\Disposable;

use Rx\TestCase;

class CallbackDisposableTest extends TestCase
{
    /**
     * @test
     */
    public function it_calls_the_callback_on_dispose()
    {
        $disposed   = false;
        $disposable = new CallbackDisposable(function() use (&$disposed) { $disposed = true; });

        $this->assertFalse($disposed);

        $disposable->dispose();

        $this->assertTrue($disposed);
    }

}
