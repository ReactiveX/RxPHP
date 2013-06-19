<?php

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
    public function it_can_be_reassigned_after_disposing()
    {
        $d1         = new CallbackDisposable(function(){});
        $d2         = new CallbackDisposable(function(){});
        $disposable = new SingleAssignmentDisposable();

        $disposable->setDisposable($d2);
        $disposable->dispose();

        $this->assertNull($disposable->getDisposable());

        $disposable->setDisposable($d1);

        $this->assertEquals($d1, $disposable->getDisposable());
    }

}
