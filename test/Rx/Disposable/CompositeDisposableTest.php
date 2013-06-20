<?php

namespace Rx\Disposable;

use Rx\TestCase;

class CompositeDisposableTest extends TestCase
{
    /**
     * @test
     */
    public function it_exposes_the_amount_of_disposables_composed()
    {
        $d1 = new CallbackDisposable(function(){});
        $d2 = new CallbackDisposable(function(){});
        $disposable = new CompositeDisposable(array($d1, $d2));

        $this->assertEquals(2, $disposable->count());
    }

    /**
     * @test
     */
    public function it_can_be_checked_if_it_contains_a_disposable()
    {
        $d1 = new CallbackDisposable(function(){});
        $d2 = new CallbackDisposable(function(){});
        $d3 = new CallbackDisposable(function(){});
        $disposable = new CompositeDisposable(array($d1, $d2));

        $this->assertTrue($disposable->contains($d1));
        $this->assertTrue($disposable->contains($d2));
        $this->assertFalse($disposable->contains($d3));
    }

    /**
     * @test
     */
    public function a_disposable_can_be_added_after_creation()
    {
        $d1 = new CallbackDisposable(function(){});
        $d2 = new CallbackDisposable(function(){});
        $disposable = new CompositeDisposable(array($d1));

        $this->assertEquals(1, $disposable->count());
        $this->assertFalse($disposable->contains($d2));

        $disposable->add($d2);
        $this->assertEquals(2, $disposable->count());
        $this->assertTrue($disposable->contains($d2));
    }

    /**
     * @test
     */
    public function disposing_disposes_all_disposables()
    {
        $disposed1 = false;
        $disposed2 = false;
        $d1 = new CallbackDisposable(function() use (&$disposed1){ $disposed1 = true; });
        $d2 = new CallbackDisposable(function() use (&$disposed2){ $disposed2 = true; });
        $disposable = new CompositeDisposable(array($d1, $d2));

        $disposable->dispose();

        $this->assertTrue($disposed1);
        $this->assertTrue($disposed2);
    }

    /**
     * @test
     */
    public function it_disposes_newly_added_disposables_when_already_disposed()
    {
        $disposed1 = false;
        $disposed2 = false;
        $d1 = new CallbackDisposable(function() use (&$disposed1){ $disposed1 = true; });
        $d2 = new CallbackDisposable(function() use (&$disposed2){ $disposed2 = true; });
        $disposable = new CompositeDisposable(array($d1));

        $disposable->dispose();
        $disposable->add($d2);

        $this->assertTrue($disposed2);
    }

    /**
     * @test
     */
    public function a_disposable_can_be_removed()
    {
        $disposed2 = false;
        $d1 = new CallbackDisposable(function(){});
        $d2 = new CallbackDisposable(function() use (&$disposed2){ $disposed2 = true; });
        $disposable = new CompositeDisposable(array($d1, $d2));

        $disposable->remove($d2);

        $this->assertFalse($disposable->contains($d2));
    }

    /**
     * @test
     */
    public function a_removed_disposable_is_disposed()
    {
        $disposed2 = false;
        $d1 = new CallbackDisposable(function(){});
        $d2 = new CallbackDisposable(function() use (&$disposed2){ $disposed2 = true; });
        $disposable = new CompositeDisposable(array($d1, $d2));

        $this->assertTrue($disposable->remove($d2));
        $this->assertTrue($disposed2);
    }

    /**
     * @test
     */
    public function removing_when_disposed_has_no_effect()
    {
        $disposable = new CompositeDisposable(array());
        $disposable->dispose();

        $disposed1 = false;
        $d1 = new CallbackDisposable(function() use (&$disposed1){ $disposed1 = true; });

        $disposable->remove($d1);

        $this->assertFalse($disposed1);
    }

    /**
     * @test
     */
    public function removing_a_disposable_that_is_not_contained_has_no_effect()
    {
        $disposable = new CompositeDisposable(array());

        $disposed1 = false;
        $d1 = new CallbackDisposable(function() use (&$disposed1){ $disposed1 = true; });

        $disposable->remove($d1);

        $this->assertFalse($disposed1);
    }

    /**
     * @test
     */
    public function clear_disposes_all_contained_disposables_but_not_the_composite_disposable()
    {
        $disposed1 = false;
        $disposed2 = false;
        $d1 = new CallbackDisposable(function() use (&$disposed1){ $disposed1 = true; });
        $d2 = new CallbackDisposable(function() use (&$disposed2){ $disposed2 = true; });
        $disposable = new CompositeDisposable(array($d1, $d2));

        $disposable->clear();

        $this->assertTrue($disposed1);
        $this->assertTrue($disposed2);

        $disposed3 = false;
        $d3 = new CallbackDisposable(function() use (&$disposed3){ $disposed3 = true; });

        $disposable->add($d3);
        $this->assertFalse($disposed3);
    }

    /**
     * @test
     */
    public function it_can_be_disposed_multiple_times()
    {
        $d1 = new CallbackDisposable(function(){});
        $d2 = new CallbackDisposable(function(){});
        $disposable = new CompositeDisposable(array($d1, $d2));

        $disposable->dispose();
        $disposable->dispose();
    }
}
