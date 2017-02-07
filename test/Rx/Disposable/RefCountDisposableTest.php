<?php

declare(strict_types = 1);

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

    /**
     * @test
     */
    public function it_does_not_dispose_the_primary_if_refcount_inner_disposable_is_disposed_multiple_times()
    {
        $d = new BooleanDisposable();
        $r = new RefCountDisposable($d);

        $d1 = $r->getDisposable();
        $d2 = $r->getDisposable();

        $d1->dispose();
        $this->assertFalse($d->isDisposed());

        $d1->dispose();
        $this->assertFalse($d->isDisposed());

        $r->dispose();
        $this->assertFalse($d->isDisposed());

        $d2->dispose();
        $this->assertTrue($d->isDisposed());
    }

    /**
     * @test
     */
    public function it_does_not_dispose_the_primary_if_already_disposed_via_refcount()
    {
        $called = 0;
        $d = new CallbackDisposable(function() use (&$called) { $called++; });
        $r = new RefCountDisposable($d);

        $r->dispose();
        $this->assertEquals(1, $called);
        $r->dispose();
        $this->assertEquals(1, $called);
    }

    /**
     * @test
     */
    public function it_does_not_dispose_the_primary_if_already_disposed()
    {
        $called = 0;
        $d = new CallbackDisposable(function() use (&$called) { $called++; });
        $r = new RefCountDisposable($d);

        $d1 = $r->getDisposable();

        $d1->dispose();
        $r->dispose();
        $this->assertEquals(1, $called);

        $d1->dispose();
        $this->assertEquals(1, $called);
    }

    /**
     * @test
     */
    public function it_returns_a_noop_disposable_if_primary_is_already_disposed()
    {
        $called = 0;
        $d = new CallbackDisposable(function() use (&$called) { $called++; });
        $r = new RefCountDisposable($d);

        $r->dispose();
        $this->assertEquals(1, $called);

        $d1 = $r->getDisposable();
        $d1->dispose();
        $r->dispose();
        $this->assertEquals(1, $called);
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
