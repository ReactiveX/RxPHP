<?php

declare(strict_types = 1);


namespace Rx\Disposable;

use Rx\TestCase;

class BinaryDisposableTest extends TestCase
{
    /**
     * @test
     */
    public function it_disposes_the_binary_disposable()
    {
        $disposed1 = false;

        $d1 = new CallbackDisposable(function () use (&$disposed1) {
            $disposed1 = true;
        });

        $disposed2 = false;

        $d2 = new CallbackDisposable(function () use (&$disposed2) {
            $disposed2 = true;
        });

        $disposable = new BinaryDisposable($d1, $d2);

        $this->assertFalse($disposed1);
        $this->assertFalse($disposed2);

        $disposable->dispose();

        $this->assertTrue($disposed1);
        $this->assertTrue($disposed2);

        $this->assertTrue($disposable->isDisposed());
    }

    /**
     * @test
     */
    public function it_does_nothing_if_disposed_twice()
    {
        $disposed1 = 0;

        $d1 = new CallbackDisposable(function () use (&$disposed1) {
            $disposed1++;
        });

        $disposed2 = 0;

        $d2 = new CallbackDisposable(function () use (&$disposed2) {
            $disposed2++;
        });

        $disposable = new BinaryDisposable($d1, $d2);

        $this->assertEquals(0, $disposed1);
        $this->assertEquals(0, $disposed2);

        $disposable->dispose();

        $this->assertEquals(1, $disposed1);
        $this->assertEquals(1, $disposed2);

        $this->assertTrue($disposable->isDisposed());

        $disposable->dispose();

        $this->assertEquals(1, $disposed1);
        $this->assertEquals(1, $disposed2);

        $this->assertTrue($disposable->isDisposed());
    }
}