<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Testing\MockObserver;

class NeverTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function never_basic()
    {
        $xs = Observable::never();

        $results = new MockObserver($this->scheduler);

        $xs->subscribe($results);

        $this->assertMessages([], $results->getMessages());
    }
}
