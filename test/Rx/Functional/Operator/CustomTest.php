<?php

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class CustomTest extends FunctionalTestCase
{
    public function testCustomOperator()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return Observable::just(1, $this->scheduler)
                ->customTest(2);
        });
        
        $this->assertMessages([
            onNext(201, 2),
            onCompleted(201)
        ], $results->getMessages());
    }
}