<?php

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class CustomTest extends FunctionalTestCase
{
    public function testCustomOperator()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return Observable::just(1)
                ->customTest(2);
        });

        $this->assertMessages([
            onNext(201, 2),
            onCompleted(201)
        ], $results->getMessages());
    }

    public function testExternalNamespacedOperator()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return Observable::just(1)
                ->_CustomOperatorTest_test(2);
        });

        $this->assertMessages([
            onNext(201, 2),
            onCompleted(201)
        ], $results->getMessages());
    }

    public function testExternalNestedNamespacedOperator()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return Observable::just(1)
                ->_CustomOperatorTest_SubNamespace_test(2);
        });

        $this->assertMessages([
            onNext(201, 2),
            onCompleted(201)
        ], $results->getMessages());
    }
}