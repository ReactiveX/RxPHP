<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;

class FlatMapLatestTest extends FunctionalTestCase
{

    /**
     * @test
     */
    public function flatMapLatest_empty()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230)
        ]);

        $ys = $this->createHotObservable([
            onNext(50, 'foo'),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->flatMapLatest(function () use ($ys) {
                return $ys;
            });
        });

        $this->assertMessages([
            onCompleted(230)
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 230)], $xs->getSubscriptions());
        $this->assertSubscriptions([], $ys->getSubscriptions());
    }


    /**
     * @test
     */
    public function flatMapLatest_inner_empty()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(210, 2),
            onCompleted(230)
        ]);

        $ys = $this->createHotObservable([
            onNext(50, 'foo'),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->flatMapLatest(function () use ($ys) {
                return $ys;
            });
        });

        $this->assertMessages([
            onCompleted(250)
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 230)], $xs->getSubscriptions());
        $this->assertSubscriptions([subscribe(210, 250)], $ys->getSubscriptions());
    }


    /**
     * @test
     */
    public function flatMapLatest_many()
    {
        $xs = $this->createColdObservable([
            onNext(100, 4),
            onNext(200, 2),
            onNext(300, 3),
            onNext(400, 1),
            onCompleted(500)
        ]);

        $ys = $this->createColdObservable([
            onNext(50, 'foo'),
            onNext(100, 'bar'),
            onNext(150, 'baz'),
            onNext(200, 'qux'),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->flatMapLatest(function () use ($ys) {
                return $ys;
            });
        });

        $this->assertMessages([
            onNext(350, 'foo'),
            onNext(450, 'foo'),
            onNext(550, 'foo'),
            onNext(650, 'foo'),
            onNext(700, 'bar'),
            onNext(750, 'baz'),
            onNext(800, 'qux'),
            onCompleted(850)
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 700)], $xs->getSubscriptions());
        $this->assertSubscriptions([
            subscribe(300, 400),
            subscribe(400, 500),
            subscribe(500, 600),
            subscribe(600, 850)
        ], $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function flatMapLatest_errors()
    {
        $error = new \Exception();

        $xs = $this->createColdObservable([
            onNext(100, 4),
            onNext(200, 2),
            onNext(300, 3),
            onError(400, $error),
        ]);

        $ys = $this->createColdObservable([
            onNext(50, 'foo'),
            onNext(100, 'bar'),
            onNext(150, 'baz'),
            onNext(200, 'qux'),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->flatMapLatest(function () use ($ys) {
                return $ys;
            });
        });

        $this->assertMessages([
            onNext(350, 'foo'),
            onNext(450, 'foo'),
            onNext(550, 'foo'),
            onError(600, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 600)], $xs->getSubscriptions());
        $this->assertSubscriptions([
            subscribe(300, 400),
            subscribe(400, 500),
            subscribe(500, 600)
        ], $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function flatMapLatest_inner_errors()
    {
        $error = new \Exception();

        $xs = $this->createColdObservable([
            onNext(100, 4),
            onNext(200, 2),
            onNext(300, 3),
            onNext(400, 1),
            onCompleted(500)
        ]);

        $ys = $this->createColdObservable([
            onNext(50, 'foo'),
            onError(100, $error)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->flatMapLatest(function () use ($ys) {
                return $ys;
            });
        });

        $this->assertMessages([
            onNext(350, 'foo'),
            onNext(450, 'foo'),
            onNext(550, 'foo'),
            onNext(650, 'foo'),
            onError(700, $error)
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 700)], $xs->getSubscriptions());
        $this->assertSubscriptions([
            subscribe(300, 400),
            subscribe(400, 500),
            subscribe(500, 600),
            subscribe(600, 700)
        ], $ys->getSubscriptions());

    }

    /**
     * @test
     */
    public function flatMapLatest_throws()
    {
        $xs = $this->createColdObservable([
            onNext(100, 4),
            onNext(200, 2),
            onNext(300, 3),
            onNext(400, 1),
            onCompleted(500)
        ]);

        $ys = $this->createColdObservable([
            onNext(50, 'foo'),
            onNext(100, 'bar'),
            onNext(150, 'baz'),
            onNext(200, 'qux'),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->flatMapLatest(function () use ($ys) {
                throw new \Exception('error');
            });
        });

        $this->assertMessages([
            onError(300, new \Exception('error'))
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 300)], $xs->getSubscriptions());
        $this->assertSubscriptions([], $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function flatMapLatest_returns_invalid_string()
    {
        $xs = $this->createColdObservable([
            onNext(100, 4),
            onNext(200, 2),
            onNext(300, 3),
            onNext(400, 1),
            onCompleted(500)
        ]);

        $ys = $this->createColdObservable([
            onNext(50, 'foo'),
            onNext(100, 'bar'),
            onNext(150, 'baz'),
            onNext(200, 'qux'),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs, $ys) {
            return $xs->flatMapLatest(function () use ($ys) {
                return 'unexpected string';
            });
        });

        $this->assertMessages([
            onError(300, new \Exception())
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 300)], $xs->getSubscriptions());
        $this->assertSubscriptions([], $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function flatMapLatest_dispose()
    {
        $xs = $this->createColdObservable([
            onNext(100, 4),
            onNext(200, 2),
            onNext(300, 3),
            onNext(400, 1),
            onCompleted(500)
        ]);

        $ys = $this->createColdObservable([
            onNext(50, 'foo'),
            onNext(100, 'bar'),
            onNext(150, 'baz'),
            onNext(200, 'qux'),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs, $ys) {
            return $xs->flatMapLatest(function () use ($ys) {
                return $ys;
            });
        }, 760);

        $this->assertMessages([
            onNext(350, 'foo'),
            onNext(450, 'foo'),
            onNext(550, 'foo'),
            onNext(650, 'foo'),
            onNext(700, 'bar'),
            onNext(750, 'baz')
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 700)], $xs->getSubscriptions());
        $this->assertSubscriptions([
            subscribe(300, 400),
            subscribe(400, 500),
            subscribe(500, 600),
            subscribe(600, 760)
        ], $ys->getSubscriptions());
    }

    /**
     * @test
     */
    public function flatMapLatest_dispose_before_outer_completes()
    {
        $xs = $this->createColdObservable([
            onNext(100, 4),
            onNext(200, 2),
            onNext(300, 3),
            onNext(400, 1),
            onCompleted(500)
        ]);

        $ys = $this->createColdObservable([
            onNext(50, 'foo'),
            onNext(100, 'bar'),
            onNext(150, 'baz'),
            onNext(200, 'qux'),
            onCompleted(250)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs, $ys) {
            return $xs->flatMapLatest(function () use ($ys) {
                return $ys;
            });
        }, 660);

        $this->assertMessages([
            onNext(350, 'foo'),
            onNext(450, 'foo'),
            onNext(550, 'foo'),
            onNext(650, 'foo')
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 660)], $xs->getSubscriptions());
        $this->assertSubscriptions([
            subscribe(300, 400),
            subscribe(400, 500),
            subscribe(500, 600),
            subscribe(600, 660)
        ], $ys->getSubscriptions());
    }
}
