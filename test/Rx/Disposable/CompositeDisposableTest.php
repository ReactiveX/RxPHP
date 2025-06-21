<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\TestCase;

class CompositeDisposableTest extends TestCase
{
    /**
     * @test
     */
    public function it_exposes_the_amount_of_disposables_composed(): void
    {
        $d1 = new CallbackDisposable(function(): void{});
        $d2 = new CallbackDisposable(function(): void{});
        $disposable = new CompositeDisposable([$d1, $d2]);

        $this->assertEquals(2, $disposable->count());
    }

    /**
     * @test
     */
    public function it_can_be_checked_if_it_contains_a_disposable(): void
    {
        $d1 = new CallbackDisposable(function(): void{});
        $d2 = new CallbackDisposable(function(): void{});
        $d3 = new CallbackDisposable(function(): void{});
        $disposable = new CompositeDisposable([$d1, $d2]);

        $this->assertTrue($disposable->contains($d1));
        $this->assertTrue($disposable->contains($d2));
        $this->assertFalse($disposable->contains($d3));
    }

    /**
     * @test
     */
    public function a_disposable_can_be_added_after_creation(): void
    {
        $d1 = new CallbackDisposable(function(): void{});
        $d2 = new CallbackDisposable(function(): void{});
        $disposable = new CompositeDisposable([$d1]);

        $this->assertEquals(1, $disposable->count());
        $this->assertFalse($disposable->contains($d2));

        $disposable->add($d2);
        $this->assertEquals(2, $disposable->count());
        $this->assertTrue($disposable->contains($d2));
    }

    /**
     * @test
     */
    public function disposing_disposes_all_disposables(): void
    {
        $disposed1 = false;
        $disposed2 = false;
        $d1 = new CallbackDisposable(function() use (&$disposed1): void{ $disposed1 = true; });
        $d2 = new CallbackDisposable(function() use (&$disposed2): void{ $disposed2 = true; });
        $disposable = new CompositeDisposable([$d1, $d2]);

        $disposable->dispose();

        $this->assertTrue($disposed1);
        $this->assertTrue($disposed2);
    }

    /**
     * @test
     */
    public function disposing_disposes_all_disposables_only_once(): void
    {
        $disposed1 = 0;
        $disposed2 = 0;
        $d1 = new CallbackDisposable(function() use (&$disposed1): void{ $disposed1++; });
        $d2 = new CallbackDisposable(function() use (&$disposed2): void{ $disposed2++; });
        $disposable = new CompositeDisposable([$d1, $d2]);

        $this->assertEquals(0, $disposed1);
        $this->assertEquals(0, $disposed2);

        $disposable->dispose();

        $this->assertEquals(1, $disposed1);
        $this->assertEquals(1, $disposed2);

        $disposable->dispose();

        $this->assertEquals(1, $disposed1);
        $this->assertEquals(1, $disposed2);
    }

    /**
     * @test
     */
    public function it_disposes_newly_added_disposables_when_already_disposed(): void
    {
        $disposed1 = false;
        $disposed2 = false;
        $d1 = new CallbackDisposable(function() use (&$disposed1): void{ $disposed1 = true; });
        $d2 = new CallbackDisposable(function() use (&$disposed2): void{ $disposed2 = true; });
        $disposable = new CompositeDisposable([$d1]);

        $disposable->dispose();
        $disposable->add($d2);

        $this->assertTrue($disposed2);
    }

    /**
     * @test
     */
    public function a_disposable_can_be_removed(): void
    {
        $disposed2 = false;
        $d1 = new CallbackDisposable(function(): void{});
        $d2 = new CallbackDisposable(function() use (&$disposed2): void{ $disposed2 = true; });
        $disposable = new CompositeDisposable([$d1, $d2]);

        $disposable->remove($d2);

        $this->assertFalse($disposable->contains($d2));
    }

    /**
     * @test
     */
    public function a_removed_disposable_is_disposed(): void
    {
        $disposed2 = false;
        $d1 = new CallbackDisposable(function(): void{});
        $d2 = new CallbackDisposable(function() use (&$disposed2): void{ $disposed2 = true; });
        $disposable = new CompositeDisposable([$d1, $d2]);

        $this->assertTrue($disposable->remove($d2));
        $this->assertTrue($disposed2);
    }

    /**
     * @test
     */
    public function removing_when_disposed_has_no_effect(): void
    {
        $disposable = new CompositeDisposable([]);
        $disposable->dispose();

        $disposed1 = false;
        $d1 = new CallbackDisposable(function() use (&$disposed1): void{ $disposed1 = true; });

        $removed = $disposable->remove($d1);

        $this->assertFalse($disposed1);

        $this->assertFalse($removed);
    }

    /**
     * @test
     */
    public function removing_a_disposable_that_is_not_contained_has_no_effect(): void
    {
        $disposable = new CompositeDisposable([]);

        $disposed1 = false;
        $d1 = new CallbackDisposable(function() use (&$disposed1): void{ $disposed1 = true; });

        $removed = $disposable->remove($d1);

        $this->assertFalse($disposed1);

        $this->assertFalse($removed);
    }

    /**
     * @test
     */
    public function clear_disposes_all_contained_disposables_but_not_the_composite_disposable(): void
    {
        $disposed1 = false;
        $disposed2 = false;
        $d1 = new CallbackDisposable(function() use (&$disposed1): void{ $disposed1 = true; });
        $d2 = new CallbackDisposable(function() use (&$disposed2): void{ $disposed2 = true; });
        $disposable = new CompositeDisposable([$d1, $d2]);

        $disposable->clear();

        $this->assertTrue($disposed1);
        $this->assertTrue($disposed2);

        $disposed3 = false;
        $d3 = new CallbackDisposable(function() use (&$disposed3): void{ $disposed3 = true; });

        $disposable->add($d3);
        $this->assertFalse($disposed3);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_can_be_disposed_multiple_times(): void
    {
        $d1 = new CallbackDisposable(function(): void{});
        $d2 = new CallbackDisposable(function(): void{});
        $disposable = new CompositeDisposable([$d1, $d2]);

        $disposable->dispose();
        $disposable->dispose();
    }

    /**
     * @test
     *
     * see https://github.com/ReactiveX/RxPHP/issues/107
     */
    public function it_can_distinguish_and_dispose_of_correct_disposable(): void
    {
        // factory to create 2 disposables that evaluate the same with ==
        $getSimilarDisposables = function () {
            $dispA = new SerialDisposable();
            $dispB = new SerialDisposable();

            return [$dispA, $dispB];
        };

        /** @var SerialDisposable[] $disposables */
        $disposables = $getSimilarDisposables();
        $compositeDisposable = new CompositeDisposable($disposables);

        // all future sets of disp should immediately dispose
        $compositeDisposable->remove($disposables[0]);
        $wasDisposed = false;
        $disposables[0]->setDisposable(new CallbackDisposable(function () use (&$wasDisposed): void {
            $wasDisposed = true;
        }));
        $this->assertTrue($wasDisposed);

        // now try with the second one in the array (start from the beginning again)
        /** @var SerialDisposable[] $disposables */
        $disposables = $getSimilarDisposables();
        $compositeDisposable = new CompositeDisposable($disposables);

        // all future sets of disp should immediately dispose
        $compositeDisposable->remove($disposables[1]);
        $wasDisposed = false;
        $disposables[1]->setDisposable(new CallbackDisposable(function () use (&$wasDisposed): void {
            $wasDisposed = true;
        }));
        $this->assertTrue($wasDisposed);
    }

    /**
     * @test
     */
    public function it_knows_what_it_contains(): void
    {
        // factory to create 2 disposables that evaluate the same with ==
        $getSimilarDisposables = function () {
            $dispA = new SerialDisposable();
            $dispB = new SerialDisposable();

            return [$dispA, $dispB];
        };

        $disposables = $getSimilarDisposables();
        $compositeDisposable = new CompositeDisposable($disposables);

        $compositeDisposable->remove($disposables[0]);
        $this->assertFalse($compositeDisposable->contains($disposables[0]));
        $this->assertTrue($compositeDisposable->contains($disposables[1]));
        $this->assertFalse($compositeDisposable->contains(new SerialDisposable()));

        // try the second one
        $disposables = $getSimilarDisposables();
        $compositeDisposable = new CompositeDisposable($disposables);

        $compositeDisposable->remove($disposables[1]);
        $this->assertTrue($compositeDisposable->contains($disposables[0]));
        $this->assertFalse($compositeDisposable->contains($disposables[1]));
        $this->assertFalse($compositeDisposable->contains(new SerialDisposable()));
    }
}
