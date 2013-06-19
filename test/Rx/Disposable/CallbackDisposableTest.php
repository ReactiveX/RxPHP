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

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function it_can_only_be_constructed_with_a_callable()
    {
        new CallbackDisposable('asm89');
    }
}
