<?php

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable\BaseObservable;
use Rx\Testing\MockObserver;

class NeverTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function never_basic()
    {
        $xs = BaseObservable::never();

        $results = new MockObserver($this->scheduler);

        $xs->subscribe($results);

        $this->assertMessages([], $results->getMessages());
    }
}
