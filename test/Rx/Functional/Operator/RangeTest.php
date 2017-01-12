<?php

declare(strict_types = 1);


namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class RangeTest extends FunctionalTestCase
{

    /**
     * @test
     */
    public function range_zero()
    {

        $results = $this->scheduler->startWithCreate(function () {
            return Observable::range(0, 0, $this->scheduler);
        });

        $this->assertMessages(
            [
                onCompleted(201)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function range_one()
    {

        $results = $this->scheduler->startWithCreate(function () {
            return Observable::range(0, 1, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(201, 0),
                onCompleted(202)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function range_five()
    {

        $results = $this->scheduler->startWithCreate(function () {
            return Observable::range(10, 5, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(201, 10),
                onNext(202, 11),
                onNext(203, 12),
                onNext(204, 13),
                onNext(205, 14),
                onCompleted(206)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function range_dispose()
    {

        $results = $this->scheduler->startWithDispose(function () {
            return Observable::range(-10, 5, $this->scheduler);
        }, 204);

        $this->assertMessages(
            [
                onNext(201, -10),
                onNext(202, -9),
                onNext(203, -8)
            ],
            $results->getMessages()
        );
    }

}