<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Exception;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable\GroupedObservable;
use Rx\Testing\MockObserver;

class GroupByTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_passes_on_complete()
    {
        $xs         = $this->createHotObservableWithData();
        $keyInvoked = 0;

        $results = $this->scheduler->startWithCreate(function() use ($xs, &$keyInvoked) {
            return $xs->groupBy(function($elem) use (&$keyInvoked) {
                $keyInvoked++;
                return trim(strtolower($elem));
            })->select(function(GroupedObservable $observable) {
                return $observable->getKey();
            });
        });

        $this->assertEquals(12, $keyInvoked);

        $this->assertMessages(array(
            onNext(220, "foo"),
            onNext(270, "bar"),
            onNext(350, "baz"),
            onNext(360, "qux"),
            onCompleted(570),
        ), $results->getMessages());
    }

    /**
     * @test
     */
    public function it_passes_on_error()
    {
        $xs         = $this->createHotObservableWithData(true);
        $keyInvoked = 0;

        $results = $this->scheduler->startWithCreate(function() use ($xs, &$keyInvoked) {
            return $xs->groupBy(function($elem) use (&$keyInvoked) {
                $keyInvoked++;
                return trim(strtolower($elem));
            })->select(function(GroupedObservable $observable) {
                return $observable->getKey();
            });
        });

        $this->assertEquals(12, $keyInvoked);

        $this->assertMessages(array(
            onNext(220, "foo"),
            onNext(270, "bar"),
            onNext(350, "baz"),
            onNext(360, "qux"),
            onError(570, new Exception()),
        ), $results->getMessages());
    }

    /**
     * @test
     */
    public function it_disposes_the_outer_subscription()
    {
        $xs         = $this->createHotObservableWithData(true);
        $keyInvoked = 0;

        $results = $this->scheduler->startWithDispose(function() use ($xs, &$keyInvoked) {
            return $xs->groupBy(function($elem) use (&$keyInvoked) {
                $keyInvoked++;
                return trim(strtolower($elem));
            })->select(function(GroupedObservable $observable) {
                return $observable->getKey();
            });
        }, 355);

        $this->assertEquals(5, $keyInvoked);

        $this->assertMessages(array(
            onNext(220, "foo"),
            onNext(270, "bar"),
            onNext(350, "baz"),
        ), $results->getMessages());
    }

    /**
     * @test
     */
    public function it_calls_on_error_if_keyselector_throws()
    {
        $xs         = $this->createHotObservableWithData(true);
        $keyInvoked = 0;

        $results = $this->scheduler->startWithCreate(function() use ($xs, &$keyInvoked) {
            return $xs->groupBy(function($elem) use (&$keyInvoked) {
                $keyInvoked++;

                if ($keyInvoked === 10) {
                    throw new Exception();
                }

                return trim(strtolower($elem));
            })->select(function(GroupedObservable $observable) {
                return $observable->getKey();
            });
        });

        $this->assertEquals(10, $keyInvoked);

        $this->assertMessages(array(
            onNext(220, "foo"),
            onNext(270, "bar"),
            onNext(350, "baz"),
            onNext(360, "qux"),
            onError(480, new Exception()),
        ), $results->getMessages());
    }

    /**
     * @test
     */
    public function it_calls_on_error_if_elementselector_throws()
    {
        $xs             = $this->createHotObservableWithData(true);
        $elementInvoked = 0;

        $results = $this->scheduler->startWithCreate(function() use ($xs, &$elementInvoked) {
            return $xs->groupBy(function($elem) {
                return trim(strtolower($elem));
            }, function($element) use (&$elementInvoked) {
                $elementInvoked++;

                if ($elementInvoked === 10) {
                    throw new Exception('');
                }

                return $element;
            })->select(function(GroupedObservable $observable) {
                return $observable->getKey();
            });
        });

        $this->assertEquals(10, $elementInvoked);

        $this->assertMessages(array(
            onNext(220, "foo"),
            onNext(270, "bar"),
            onNext(350, "baz"),
            onNext(360, "qux"),
            onError(480, new Exception()),
        ), $results->getMessages());
    }

    /**
     * @test
     */
    public function it_calls_on_error_if_durationselector_throws()
    {
        $xs             = $this->createHotObservableWithData(true);

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->groupByUntil(function($elem) { return $elem; },
                null,
                function() { throw new Exception(''); }
            )->select(function(GroupedObservable $observable) {
                return $observable->getKey();
            });
        });

        $this->assertMessages(array(
            onError(220, new Exception()),
        ), $results->getMessages());
    }


    /**
     * @test
     */
    public function it_passes_on_error_if_duration_observable_calls_on_error()
    {
        $xs = $this->createHotObservableWithData();

        $results = $this->scheduler->startWithCreate(function() use ($xs) {
            return $xs->groupByUntil(function($elem) {
                    return trim(strtolower($elem));
                },
                null,
                function(GroupedObservable $observable) {
                    return $observable->select(function() { throw new Exception('meh.'); });
            })->select(function(GroupedObservable $observable) {
                return $observable->getKey();
            });
        });

        $this->scheduler->start();
        $this->assertMessages(array(
            onNext(220, 'foo'),
            onError(220, new Exception()),
        ), $results->getMessages());
    }

    /**
     * @test
     */
    public function it_calls_on_completed_on_inner_subscription_if_subscription_expires_otherwise_it_passes()
    {
        $xs = $this->createHotObservableWithData();

        $observable = $xs->groupByUntil(function($elem) {
                return trim(strtolower($elem));
            },
            null,
            function(GroupedObservable $observable) {
                return $observable->skip(2);
            }
        );

        $innerSubscriptions = array();
        $scheduler = $this->scheduler;
        $observable->subscribe(function(GroupedObservable $observable) use (&$innerSubscriptions, $scheduler) {
            $observer = new MockObserver($scheduler);
            $observable->subscribe($observer);

            $innerSubscriptions[] = array(
                'key'      => $observable->getKey(),
                'observer' => $observer,
            );
        });

        $this->scheduler->start();

        // on complete gets called after duration is "over"
        $this->assertCount(4, $innerSubscriptions[0]['observer']->getMessages());
        $this->assertMessages(array(
            onNext(90, "error"),
            onNext(110, "error"),
            onNext(130, "error"),
            onCompleted(130),
        ), $innerSubscriptions[0]['observer']->getMessages());

        $this->assertCount(4, $innerSubscriptions[1]['observer']->getMessages());
        $this->assertMessages(array(
            onNext(220, "  foo"),
            onNext(240, " FoO "),
            onNext(310, "foO "),
            onCompleted(310),
        ), $innerSubscriptions[1]['observer']->getMessages());

        $this->assertCount(4, $innerSubscriptions[2]['observer']->getMessages());
        $this->assertMessages(array(
            onNext(270, "baR  "),
            onNext(390, "   bar"),
            onNext(420, " BAR  "),
            onCompleted(420),
        ), $innerSubscriptions[2]['observer']->getMessages());

        $this->assertCount(4, $innerSubscriptions[3]['observer']->getMessages());
        $this->assertMessages(array(
            onNext(350, " Baz   "),
            onNext(480, "baz  "),
            onNext(510, " bAZ "),
            onCompleted(510),
        ), $innerSubscriptions[3]['observer']->getMessages());

        // Otherwise the original on complete is passed
        $this->assertCount(2, $innerSubscriptions[4]['observer']->getMessages());
        $this->assertMessages(array(
            onNext(360, "  qux "),
            onCompleted(570),
        ), $innerSubscriptions[4]['observer']->getMessages());

        $this->assertCount(3, $innerSubscriptions[5]['observer']->getMessages());
        $this->assertMessages(array(
            onNext(470, "FOO "),
            onNext(530, "    fOo    "),
            onCompleted(570),
        ), $innerSubscriptions[5]['observer']->getMessages());
    }

    protected function createHotObservableWithData($error = false)
    {
        return $this->createHotObservable(array(
            onNext(90, "error"),
            onNext(110, "error"),
            onNext(130, "error"),
            onNext(220, "  foo"),
            onNext(240, " FoO "),
            onNext(270, "baR  "),
            onNext(310, "foO "),
            onNext(350, " Baz   "),
            onNext(360, "  qux "),
            onNext(390, "   bar"),
            onNext(420, " BAR  "),
            onNext(470, "FOO "),
            onNext(480, "baz  "),
            onNext(510, " bAZ "),
            onNext(530, "    fOo    "),
            $error ? onError(570, new Exception()) : onCompleted(570),
            onNext(580, "error"),
            onCompleted(600),
            onError(650, new Exception()),
        ));
    }
}
