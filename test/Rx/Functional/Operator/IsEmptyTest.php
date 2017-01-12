<?php

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;

class IsEmptyTest extends FunctionalTestCase
{

    /**
     * @test
     */
    public function should_return_true_if_source_is_empty()
    {
        $xs = $this->createHotObservable([
            onCompleted(300)
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->isEmpty();
        });

        $this->assertMessages([
            onNext(300, true),
            onCompleted(300),
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 300),
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function should_return_false_if_source_emits_element()
    {
        $xs = $this->createHotObservable([
            onNext(150, 'a'),
            onNext(300, 'b'),
            onCompleted(300)
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->isEmpty();
        });

        $this->assertMessages([
            onNext(300, false),
            onCompleted(300),
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 300),
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function should_return_true_if_source_emits_before_subscription()
    {
        $xs = $this->createHotObservable([
            onNext(150, 'a'),
            onCompleted(300)
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->isEmpty();
        });

        $this->assertMessages([
            onNext(300, true),
            onCompleted(300),
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 300),
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function should_raise_error_if_source_raise_error()
    {
        $e = new \Exception();

        $xs = $this->createHotObservable([
            onError(300, $e),
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->isEmpty();
        });

        $this->assertMessages([
            onError(300, $e),
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 300),
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function should_not_complete_if_source_never_emits()
    {
        $xs = $this->createHotObservable([]);

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->isEmpty();
        });

        $this->assertMessages([], $results->getMessages());
        $this->assertSubscriptions([
            subscribe(200, 1000),
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function should_return_true_if_source_completes_immediately()
    {
        $xs = $this->createHotObservable([
            onCompleted(201),
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->isEmpty();
        });

        $this->assertMessages([
            onNext(201, true),
            onCompleted(201),
        ], $results->getMessages());
        $this->assertSubscriptions([
            subscribe(200, 201),
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function should_allow_unsubscribing_explicitly_and_early()
    {
        $xs = $this->createHotObservable([
            onNext(600, 'a'),
            onNext(700, 'b'),
        ]);

        $results = $this->scheduler->startWithDispose(function() use ($xs) {
            return $xs->isEmpty();
        }, 500);

        $this->assertMessages([], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 500),
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function should_not_break_unsubscription_chains_when_result_is_unsubscribed_explicitly()
    {
        $xs = $this->createHotObservable([
            onNext(600, 'a'),
            onNext(700, 'b'),
        ]);

        $results = $this->scheduler->startWithDispose(function() use ($xs) {
            return $xs
                ->flatMap(function($value) {
                    return \Rx\Observable::just($value);
                })
                ->isEmpty()
                ->flatMap(function($value) {
                    return \Rx\Observable::just($value);
                });
        }, 500);

        $this->assertMessages([], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 500),
        ], $xs->getSubscriptions());
    }

}