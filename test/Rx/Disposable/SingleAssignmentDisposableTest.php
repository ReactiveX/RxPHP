<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\TestCase;

class SingleAssignmentDisposableTest extends TestCase
{
    /**
     * @test
     */
    public function it_disposes_the_assigned_disposable()
    {
        $disposed1   = false;
        $d1         = new CallbackDisposable(function() use (&$disposed1){ $disposed1 = true; });
        $disposable = new SingleAssignmentDisposable();

        $disposable->setDisposable($d1);

        $this->assertFalse($disposed1);

        $disposable->dispose();

        $this->assertTrue($disposed1);
    }

    /**
     * @test
     */
    public function it_disposes_newly_set_disposable_if_already_disposed()
    {
        $disposed1   = false;
        $d1         = new CallbackDisposable(function() use (&$disposed1){ $disposed1 = true; });
        $d2         = new CallbackDisposable(function(){});
        $disposable = new SingleAssignmentDisposable();

        $disposable->setDisposable($d2);
        $disposable->dispose();

        $this->assertNull($disposable->getDisposable());

        $disposable->setDisposable($d1);

        $this->assertTrue($disposed1);
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function it_cannot_be_assignmed_multiple_times()
    {
        $d1         = new CallbackDisposable(function(){});
        $d2         = new CallbackDisposable(function(){});
        $disposable = new SingleAssignmentDisposable();

        $disposable->setDisposable($d1);
        $disposable->setDisposable($d2);
    }
}
