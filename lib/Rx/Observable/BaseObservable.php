<?php

namespace Rx\Observable;

use Exception;
use InvalidArgumentException;
use Rx\ObserverInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\Operator\DistinctUntilChangedOperator;
use Rx\Operator\NeverOperator;
use Rx\Operator\OperatorInterface;
use Rx\Operator\ScanOperator;
use Rx\Operator\SkipLastOperator;
use Rx\Operator\TapOperator;
use Rx\Operator\ToArrayOperator;
use Rx\Scheduler\ImmediateScheduler;
use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\SingleAssignmentDisposable;
use Rx\SchedulerInterface;
use Rx\Subject\Subject;
use Rx\Disposable\RefCountDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\CallbackDisposable;

abstract class BaseObservable implements ObservableInterface
{
    protected $observers = array();
    protected $started = false;
    private $disposable = null;

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $this->observers[] = $observer;

        if ( ! $this->started) {
            $this->start($scheduler);
        }

        $observable = $this;

        return new CallbackDisposable(function() use ($observer, $observable) {
            $observable->removeObserver($observer);
        });
    }

    /**
     * @internal
     */
    public function removeObserver(ObserverInterface $observer)
    {
        $key = array_search($observer, $this->observers);

        if (false === $key) {
            return false;
        }

        unset($this->observers[$key]);

        return true;
    }

    public function subscribeCallback($onNext = null, $onError = null, $onCompleted = null, $scheduler = null)
    {
        $observer = new CallbackObserver($onNext, $onError, $onCompleted);

        return $this->subscribe($observer, $scheduler);
    }

    private function start($scheduler = null)
    {
        if (null === $scheduler) {
            $scheduler = new ImmediateScheduler();
        }

        $this->started = true;

        $this->doStart($scheduler);
    }

    abstract protected function doStart($scheduler);

    public function select($selector)
    {
        if ( ! is_callable($selector)) {
            throw new InvalidArgumentException('Selector should be a callable.');
        }

        $currentObservable = $this;

        // todo: add scheduler
        return new AnonymousObservable(function($observer, $scheduler) use ($currentObservable, $selector) {
            $selectObserver = new CallbackObserver(
                function($nextValue) use ($observer, $selector) {
                    $value = null;
                    try {
                        $value = $selector($nextValue);
                    } catch (Exception $e) {
                        $observer->onError($e);
                    }
                    $observer->onNext($value);
                },
                function($error) use ($observer) {
                    $observer->onError($error);
                },
                function() use ($observer) {
                    $observer->onCompleted();
                }
            );

            return $currentObservable->subscribe($selectObserver, $scheduler);
        });
    }

    public function where($predicate)
    {
        if ( ! is_callable($predicate)) {
            throw new InvalidArgumentException('Predicate should be a callable.');
        }

        $currentObservable = $this;

        // todo: add scheduler
        return new AnonymousObservable(function($observer, $scheduler) use ($currentObservable, $predicate) {
            $selectObserver = new CallbackObserver(
                function($nextValue) use ($observer, $predicate) {
                    $shouldFire = false;
                    try {
                        $shouldFire = $predicate($nextValue);
                    } catch (Exception $e) {
                        $observer->onError($e);
                    }

                    if ($shouldFire) {
                        $observer->onNext($nextValue);
                    }
                },
                function($error) use ($observer) {
                    $observer->onError($error);
                },
                function() use ($observer) {
                    $observer->onCompleted();
                }
            );

            return $currentObservable->subscribe($selectObserver, $scheduler);
        });
    }

    public function merge(ObservableInterface $otherObservable, $scheduler = null)
    {
        return self::mergeAll(
            self::fromArray(array($this, $otherObservable), $scheduler)
        );
    }

    public function selectMany($selector)
    {
        if ( ! is_callable($selector)) {
            throw new InvalidArgumentException('Selector should be a callable.');
        }

        return self::mergeAll($this->select($selector));
    }

    /**
     * Merges an observable sequence of observables into an observable sequence.
     *
     * @param ObservableInterface $observables
     *
     * @return ObserverInterface
     */
    public static function mergeAll(ObservableInterface $sources)
    {
        // todo: add scheduler
        return new AnonymousObservable(function($observer, $scheduler) use ($sources) {
            $group              = new CompositeDisposable();
            $isStopped          = false;
            $sourceSubscription = new SingleAssignmentDisposable();

            $group->add($sourceSubscription);

            $sourceSubscription->setDisposable(
                $sources->subscribeCallback(
                    function($innerSource) use (&$group, &$isStopped, $observer, &$scheduler) {
                        $innerSubscription = new SingleAssignmentDisposable();
                        $group->add($innerSubscription);

                        $innerSubscription->setDisposable(
                            $innerSource->subscribeCallback(
                                function($nextValue) use ($observer) {
                                    $observer->onNext($nextValue);
                                },
                                function($error) use ($observer) {
                                    $observer->onError($error);
                                },
                                function() use (&$group, &$innerSubscription, &$isStopped, $observer) {
                                    $group->remove($innerSubscription);

                                    if ($isStopped && $group->count() === 1) {
                                        $observer->onCompleted();
                                    }
                                },
                                $scheduler
                            )
                        );
                    },
                    function($error) use ($observer) {
                        $observer->onError($error);
                    },
                    function() use (&$group, &$isStopped, $observer) {
                        $isStopped = true;
                        if ($group->count() === 1) {
                            $observer->onCompleted();
                        }
                    },
                    $scheduler
                )
            );

            return $group;
        });
    }

    public static function fromArray(array $array)
    {
        $max   = count($array);
        return new AnonymousObservable(function ($observer, $scheduler) use ($array, $max) {
            $count = 0;

            return $scheduler->scheduleRecursive(function($reschedule) use (&$count, $array, $max, $observer) {
                if ($count < $max) {
                    $observer->onNext($array[$count]);
                    $count++;
                    $reschedule();
                } else {
                    $observer->onCompleted();
                }
            });
        });
    }

    public function skip($count)
    {
        if ($count < 0) {
            throw new InvalidArgumentException('Count must be >= 0');
        }

        $currentObservable = $this;

        return new AnonymousObservable(function($observer, $scheduler) use ($currentObservable, $count) {
            $remaining = $count;

            return $currentObservable->subscribeCallback(
                function($nextValue) use ($observer, &$remaining) {
                    if ($remaining <= 0) {
                        $observer->onNext($nextValue);
                    } else {
                        $remaining--;
                    }
                },
                array($observer, 'onError'),
                array($observer, 'onCompleted')
            );
        });
    }

    public function take($count)
    {
        if ($count < 0) {
            throw new InvalidArgumentException('Count must be >= 0');
        }

        if ($count === 0) {
            return new EmptyObservable();
        }

        $currentObservable = $this;

        return new AnonymousObservable(function($observer, $scheduler) use ($currentObservable, $count) {
            $remaining = $count;

            return $currentObservable->subscribeCallback(
                function($nextValue) use ($observer, &$remaining) {
                    if ($remaining > 0) {
                        $remaining--;
                        $observer->onNext($nextValue);
                        if ($remaining === 0) {
                            $observer->onCompleted();
                        }
                    }
                },
                array($observer, 'onError'),
                array($observer, 'onCompleted'),
                $scheduler
            );
        });
    }

    public function groupBy($keySelector, $elementSelector = null, $keySerializer = null)
    {
        return $this->groupByUntil($keySelector, $elementSelector, function() {

            // observable that never calls
            return new AnonymousObservable(function() {
                // todo?
                return new EmptyDisposable();
            });
        }, $keySerializer);
    }

    public function groupByUntil($keySelector, $elementSelector = null, $durationSelector = null, $keySerializer = null)
    {
        $currentObservable = $this;

        if (null === $elementSelector) {
            $elementSelector = function($elem) { return $elem; };
        } else if ( ! is_callable($elementSelector)) {
            throw new InvalidArgumentException('Element selector should be a callable.');
        }

        if (null === $durationSelector) {
            $durationSelector = function($x) { return $x; };
        } else if ( ! is_callable($durationSelector)) {
            throw new InvalidArgumentException('Duration selector should be a callable.');
        }

        if (null === $keySerializer) {
            $keySerializer = function($x) { return $x; };
        } else if ( ! is_callable($keySerializer)) {
            throw new InvalidArgumentException('Key serializer should be a callable.');
        }

        return new AnonymousObservable(function($observer, $scheduler) use ($currentObservable, $keySelector, $elementSelector, $durationSelector, $keySerializer) {
            $map = array();
            $groupDisposable = new CompositeDisposable();
            $refCountDisposable = new RefCountDisposable($groupDisposable);

            $groupDisposable->add($currentObservable->subscribeCallback(
                function($value) use (&$map, $keySelector, $elementSelector, $durationSelector, $observer, $keySerializer, $groupDisposable, $refCountDisposable){
                    try {
                        $key = $keySelector($value);
                        $serializedKey = $keySerializer($key);
                    } catch (Exception $e) {
                        foreach ($map as $groupObserver) {
                            $groupObserver->onError($e);
                        }
                        $observer->onError($e);

                        return;
                    }

                    $fireNewMapEntry = false;

                    try {
                        if ( ! isset($map[$serializedKey])) {
                            $map[$serializedKey] = new Subject();
                            $fireNewMapEntry = true;
                        }
                        $writer = $map[$serializedKey];

                    } catch (Exception $e) {
                        foreach ($map as $groupObserver) {
                            $groupObserver->onError($e);
                        }
                        $observer->onError($e);

                        return;
                    }

                    if ($fireNewMapEntry) {
                        $group = new GroupedObservable($key, $writer, $refCountDisposable);
                        $durationGroup = new GroupedObservable($key, $writer);

                        try {
                            $duration = $durationSelector($durationGroup);
                        } catch (Exception $e) {
                            foreach ($map as $groupObserver) {
                                $groupObserver->onError($e);
                            }
                            $observer->onError($e);

                            return;
                        }

                        $observer->onNext($group);
                        $md = new SingleAssignmentDisposable();
                        $groupDisposable->add($md);
                        $expire = function() use (&$map, &$md, $serializedKey, &$writer, &$groupDisposable) {
                            if (isset($map[$serializedKey])) {
                                unset($map[$serializedKey]);
                                $writer->onCompleted();
                            }
                            $groupDisposable->remove($md);
                        };

                        $md->setDisposable(
                            $duration->take(1)->subscribeCallback(
                                function(){},
                                function(Exception $exception) use ($map, $observer){
                                    foreach ($map as $writer) {
                                        $writer->onError($exception);
                                    }

                                    $observer->onError($exception);
                                },
                                function() use ($expire) {
                                    $expire();
                                }
                            )
                        );
                    }

                    try {
                        $element = $elementSelector($value);
                    } catch (Exception $exception) {
                        foreach ($map as $writer) {
                            $writer->onError($exception);
                        }

                        $observer->onError($exception);
                        return;
                    }
                    $writer->onNext($element);
                },
                function(Exception $error) use (&$map, $observer) {
                    foreach ($map as $writer) {
                        $writer->onError($error);
                    }

                    $observer->onError($error);
                },
                function() use (&$map, $observer) {
                    foreach ($map as $writer) {
                        $writer->onCompleted();
                    }

                    $observer->onCompleted();
                },
                $scheduler
            ));

            return $refCountDisposable;
        });
    }

    /**
     * Applies an accumulator function over an observable sequence, returning the result of the aggregation as a single element in the result sequence. The specified seed value is used as the initial accumulator value.
     *
     * @param callable $accumulator - An accumulator function to be invoked on each element.
     * @param mixed $seed [optional] - The initial accumulator value.
     * @return \Rx\Observable\AnonymousObservable - An observable sequence containing a single element with the final accumulator value.
     */
    public function reduce($accumulator, $seed = null)
    {
        if (!is_callable($accumulator)) {
            throw new InvalidArgumentException('Accumulator should be a callable.');
        }

        $hasSeed = $seed ? true : false;

        return new AnonymousObservable(function (ObserverInterface $o) use ($accumulator, $hasSeed, $seed) {
            $hasAccumulation = false;
            $accumulation    = null;
            $hasValue        = false;

            return $this->subscribeCallback(
              function ($x) use ($accumulator, $o, $hasSeed, $seed, &$hasAccumulation, &$accumulation, &$hasValue) {

                  !$hasValue && ($hasValue = true);

                  try {
                      if ($hasAccumulation) {
                          $accumulation = call_user_func($accumulator, $accumulation, $x);
                      } else {
                          $accumulation    = $hasSeed ? call_user_func($accumulator, $seed, $x) : $x;
                          $hasAccumulation = true;
                      }
                  } catch (Exception $e) {
                      $o->onError($e);
                  }
              },
              function ($e) use ($o) {
                  $o->onError($e);
              },
              function () use ($accumulator, $o, $hasSeed, $seed, &$hasAccumulation, &$accumulation, &$hasValue) {
                  $hasValue && $o->onNext($accumulation);
                  !$hasValue && $hasSeed && $o->onNext($seed);
                  !$hasValue && !$hasSeed && $o->onError(new Exception("Missing Seed and or Value"));
                  $o->onCompleted();
              }
            );

        });
    }


    /**
     * Lifts a function to the current Observable and returns a new Observable that when subscribed to will pass
     * the values of the current Observable through the Operator function.
     *
     * @param \Rx\Operator\OperatorInterface $operator
     * @return \Rx\Observable\AnonymousObservable
     */
    public function lift(OperatorInterface $operator)
    {
        return new AnonymousObservable(function (ObserverInterface $observer, SchedulerInterface $schedule) use ($operator) {
            return $operator->call($this, $observer, $schedule);
        });
    }

    /**
     * Returns an observable sequence that contains only distinct contiguous elements according to the keySelector and the comparer.
     *
     * @param null $keySelector
     * @param null $comparer
     * @return \Rx\Observable\AnonymousObservable
     */
    public function distinctUntilChanged($keySelector = null, $comparer = null)
    {
        return $this->lift(new DistinctUntilChangedOperator($keySelector, $comparer));
    }

    /**
     * @return \Rx\Observable\AnonymousObservable
     */
    public static function never()
    {
        return (new EmptyObservable())->lift(new NeverOperator());
    }

    /**
     *  Invokes an action for each element in the observable sequence and invokes an action upon graceful or exceptional termination of the observable sequence.
     *  This method can be used for debugging, logging, etc. of query behavior by intercepting the message stream to run arbitrary actions for messages on the pipeline.
     *
     * @param $observerOrOnNext
     * @param null $onError
     * @param null $onCompleted
     * @return \Rx\Observable\AnonymousObservable
     */
    public function tap($observerOrOnNext, $onError = null, $onCompleted = null)
    {
        return $this->lift(new TapOperator($observerOrOnNext, $onError, $onCompleted));
    }

    /**
     *  Applies an accumulator function over an observable sequence and returns each intermediate result. The optional seed value is used as the initial accumulator value.
     *  For aggregation behavior with no intermediate results, see Observable.aggregate.
     *
     * @param $accumulator
     * @param null $seed
     * @return AnonymousObservable
     */
    public function scan($accumulator, $seed = null)
    {
        return $this->lift(new ScanOperator($accumulator, $seed));
    }

    /**
     * Creates an array from an observable sequence.
     * @return AnonymousObservable An observable sequence containing a single element with a list containing all the elements of the source sequence.
     */
    public function toArray()
    {
        return $this->lift(new ToArrayOperator());
    }

    /**
     *  Bypasses a specified number of elements at the end of an observable sequence.
     *  This operator accumulates a queue with a length enough to store the first `count` elements. As more elements are
     *  received, elements are taken from the front of the queue and produced on the result sequence. This causes elements to be delayed.
     *
     * @param $count Number of elements to bypass at the end of the source sequence.
     * @return AnonymousObservable An observable sequence containing the source sequence elements except for the bypassed ones at the end.
     */
    public function skipLast($count)
    {
        return $this->lift(new SkipLastOperator($count));
    }
}
