<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;

class SubscribeOnTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function subscribeOn_normal()
    {
        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->subscribeOn($this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(210, 2),
                onCompleted(250)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([subscribe(201, 251)], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function subscribeOn_error()
    {

        $error = new \Exception();

        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onError(210, $error)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->subscribeOn($this->scheduler);
        });

        $this->assertMessages(
            [
                onError(210, $error)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([subscribe(201, 211)], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function subscribeOn_empty()
    {
        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onCompleted(250)
            ]
        );

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->subscribeOn($this->scheduler);
        });

        $this->assertMessages(
            [
                onCompleted(250)
            ],
            $results->getMessages()
        );

        $this->assertSubscriptions([subscribe(201, 251)], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function subscribeOn_never()
    {
        $xs = $this->createHotObservable([onNext(150, 1)]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->subscribeOn($this->scheduler);
        });

        $this->assertMessages([], $results->getMessages());

        $this->assertSubscriptions([subscribe(201, 1001)], $xs->getSubscriptions());
    }
}
