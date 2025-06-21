<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class RaceTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function race_never_2(): void
    {
        $n1 = Observable::never();
        $n2 = Observable::never();

        $results = $this->scheduler->startWithCreate(function () use ($n1, $n2) {
            return Observable::race([$n1, $n2], $this->scheduler);
        });

        $this->assertMessages(
            [],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function race_never_3(): void
    {
        $n1 = Observable::never();
        $n2 = Observable::never();
        $n3 = Observable::never();

        $results = $this->scheduler->startWithCreate(function () use ($n1, $n2, $n3) {
            return Observable::race([$n1, $n2, $n3], $this->scheduler);
        });

        $this->assertMessages(
            [],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function race_never_empty(): void
    {
        $n1 = Observable::never();
        $e  = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(225)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($n1, $e) {
            return Observable::race([$n1, $e], $this->scheduler);
        });

        $this->assertMessages(
            [
                onCompleted(225)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function race_empty_never(): void
    {
        $n1 = Observable::never();
        $e  = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(225)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($n1, $e) {
            return Observable::race([$e, $n1], $this->scheduler);
        });

        $this->assertMessages(
            [
                onCompleted(225)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function race_regular_should_dispose_loser(): void
    {
        $sourceNotDisposed = false;

        $error = new \Exception('error');

        $o1 = $this->createHotObservable([
            onNext(150, 1),
            onNext(220, 2),
            onError(230, $error)
        ])->doOnNext(function () use (&$sourceNotDisposed): void {
            $sourceNotDisposed = true;
        });

        $o2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 3),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2) {
            return Observable::race([$o1, $o2], $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(210, 3),
                onCompleted(250)
            ],
            $results->getMessages()
        );

        $this->assertFalse($sourceNotDisposed);
    }

    /**
     * @test
     */
    public function race_throws_before_election(): void
    {
        $sourceNotDisposed = false;

        $error = new \Exception('error');

        $o1 = $this->createHotObservable([
            onNext(150, 1),
            onError(210, $error)
        ]);

        $o2 = $this->createHotObservable([
            onNext(150, 1),
            onNext(220, 3),
            onCompleted(250)
        ])->doOnNext(function () use (&$sourceNotDisposed): void {
            $sourceNotDisposed = true;
        });

        $results = $this->scheduler->startWithCreate(function () use ($o1, $o2) {
            return Observable::race([$o1, $o2], $this->scheduler);
        });

        $this->assertMessages(
            [
                onError(210, $error)
            ],
            $results->getMessages()
        );

        $this->assertFalse($sourceNotDisposed);
    }

    /**
     * @test
     */
    public function race_none(): void
    {
        $results = $this->scheduler->startWithCreate(function () {
            return Observable::race([], $this->scheduler);
        });

        $this->assertMessages([onCompleted(201)], $results->getMessages());
    }

    /**
     * @test
     */
    public function race_one(): void
    {
        $e = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(225)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($e) {
            return Observable::race([$e], $this->scheduler);
        });

        $this->assertMessages([
            onNext(210, 2),
            onCompleted(225)
        ], $results->getMessages());
    }
}
