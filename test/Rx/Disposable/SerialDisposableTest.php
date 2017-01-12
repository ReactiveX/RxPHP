<?php

declare(strict_types = 1);


namespace Rx\Disposable;

use Rx\TestCase;

class SerialDisposableTest extends TestCase
{
    /**
     * @test
     */
    public function it_disposes_the_assigned_disposable()
    {
        $disposed1  = false;
        $d1         = new CallbackDisposable(function () use (&$disposed1) {
            $disposed1 = true;
        });
        $disposable = new SerialDisposable();

        $disposable->setDisposable($d1);

        $this->assertFalse($disposed1);

        $disposable->dispose();

        $this->assertTrue($disposed1);
    }

    /**
     * @test
     */
    public function it_disposes_the_assigned_disposable_on_reassignment()
    {
        $disposed1  = false;
        $d1         = new CallbackDisposable(function () use (&$disposed1) { $disposed1 = true; });
        $d2         = new EmptyDisposable();
        $disposable = new SerialDisposable();

        $disposable->setDisposable($d1);

        $this->assertFalse($disposed1);

        $disposable->setDisposable($d2);

        $this->assertTrue($disposed1);

        $this->assertSame($d2, $disposable->getDisposable());
    }

    /**
     * @test
     */
    public function it_unsets_the_disposable_on_dispose()
    {
        $disposed1  = false;
        $d1         = new CallbackDisposable(function () use (&$disposed1) {
            $disposed1 = true;
        });
        $disposable = new SerialDisposable();

        $disposable->setDisposable($d1);

        $this->assertFalse($disposed1);

        $disposable->dispose();

        $this->assertTrue($disposed1);

        $this->assertNull($disposable->getDisposable());
    }

    /**
     * @test
     */
    public function it_disposes_the_assigned_disposable_if_already_disposed()
    {
        $disposed1  = false;
        $disposed2  = false;
        $d1         = new CallbackDisposable(function() use (&$disposed1){ $disposed1 = true; });
        $d2         = new CallbackDisposable(function() use (&$disposed2){ $disposed2 = true; });
        $disposable = new SerialDisposable();

        $disposable->setDisposable($d1);

        $this->assertFalse($disposed1);

        $disposable->dispose();

        $disposable->setDisposable($d2);

        $this->assertTrue($disposed2);
    }
}