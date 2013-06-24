<?php

namespace Rx\Disposable;

use Rx\DisposableInterface;
use Rx\TestCase;

class RefCountDisposableTest extends TestCase
{
    /**
     * @test
     */
    public function it_holds_a_reference_to_one_disposable()
    {
        $d = new BooleanDisposable();
        $r = new RefCountDisposable($d);

        $this->assertFalse($d->isDisposed());
        $r->dispose();
        $this->assertTrue($d->isDisposed());

    }

    /**
     * @test
     */
    public function it_disposes_if_all_references_are_disposed()
    {
        $d = new BooleanDisposable();
        $r = new RefCountDisposable($d);

        $d1 = $r->getDisposable();
        $d2 = $r->getDisposable();

        $d1->dispose();
        $this->assertFalse($d->isDisposed());

        $d2->dispose();
        $this->assertFalse($d->isDisposed());

        $r->dispose();
        $this->assertTrue($d->isDisposed());
    }

    /**
     * @test
     */
    public function it_disposes_after_last_reference_is_disposed()
    {
        $d = new BooleanDisposable();
        $r = new RefCountDisposable($d);

        $d1 = $r->getDisposable();
        $d2 = $r->getDisposable();

        $d1->dispose();
        $this->assertFalse($d->isDisposed());

        $r->dispose();
        $this->assertFalse($d->isDisposed());

        $d2->dispose();
        $this->assertTrue($d->isDisposed());
    }
}

class BooleanDisposable implements DisposableInterface
{
    private $disposed = false;

    public function dispose()
    {
        $this->disposed = true;
    }

    public function isDisposed()
    {
        return $this->disposed;
    }
}
