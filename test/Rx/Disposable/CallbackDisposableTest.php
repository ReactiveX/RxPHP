<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\TestCase;

class CallbackDisposableTest extends TestCase
{
    /**
     * @test
     */
    public function it_calls_the_callback_on_dispose(): void
    {
        $disposed   = false;
        $disposable = new CallbackDisposable(function() use (&$disposed): void { $disposed = true; });

        $this->assertFalse($disposed);

        $disposable->dispose();

        $this->assertTrue($disposed);
    }

    /**
     * @test
     */
    public function it_only_disposes_once(): void
    {
        $disposed    = false;
        $invocations = 0;
        $disposable  = new CallbackDisposable(function () use (&$disposed, &$invocations): void {
            $invocations++;
            $disposed = true;
        });

        $this->assertFalse($disposed);

        $disposable->dispose();

        $this->assertTrue($disposed);
        $this->assertEquals(1, $invocations);

        $disposable->dispose();

        $this->assertTrue($disposed);
        $this->assertEquals(1, $invocations);
    }

}
