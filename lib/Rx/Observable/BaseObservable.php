<?php

namespace Rx\Observable;

use Exception;
use InvalidArgumentException;
use Rx\ObserverInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\Operator\AsObservableOperator;
use Rx\Operator\ConcatOperator;
use Rx\Operator\CountOperator;
use Rx\Operator\DeferOperator;
use Rx\Operator\DistinctUntilChangedOperator;
use Rx\Operator\DoOnEachOperator;
use Rx\Operator\MapOperator;
use Rx\Operator\NeverOperator;
use Rx\Operator\FilterOperator;
use Rx\Operator\OperatorInterface;
use Rx\Operator\ReduceOperator;
use Rx\Operator\ScanOperator;
use Rx\Operator\SkipLastOperator;
use Rx\Operator\SkipUntilOperator;
use Rx\Operator\ToArrayOperator;
use Rx\Scheduler\ImmediateScheduler;
use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\SingleAssignmentDisposable;
use Rx\SchedulerInterface;
use Rx\Subject\AsyncSubject;
use Rx\Subject\BehaviorSubject;
use Rx\Subject\ReplaySubject;
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

    public function map($selector)
    {
        return $this->lift(new MapOperator($selector));
    }

    /**
     * Alias for Map
     * @param $selector
     * @return \Rx\Observable\AnonymousObservable
     */
    public function select($selector)
    {
        return $this->map($selector);
    }

    /**
     * Filters the elements of an observable sequence based on a predicate by incorporating the element's index.
     *
     * @param callable $predicate
     * @return \Rx\Observable\AnonymousObservable
     */
    public function filter(callable $predicate)
    {
       return $this->lift(new FilterOperator($predicate));
    }

    /**
     * Alias for filter
     *
     * @param callable $predicate
     * @return \Rx\Observable\AnonymousObservable
     */
    public function where(callable $predicate)
    {
        return $this->filter($predicate);
    }

    public function merge(ObservableInterface $otherObservable, $scheduler = null)
    {
        return self::mergeAll(
            self::fromArray(array($this, $otherObservable), $scheduler)
        );
    }

    public function flatMap($selector)
    {
        if ( ! is_callable($selector)) {
            throw new InvalidArgumentException('Selector should be a callable.');
        }

        return self::mergeAll($this->select($selector));
    }

    /**
     * Alias for flatMap
     *
     * @param $selector
     * @return \Rx\ObserverInterface
     */
    public function selectMany($selector)
    {
        return $this->flatMap($selector);
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

        if ( ! is_callable($keySelector)) {
            throw new InvalidArgumentException('Key selector should be a callable.');
        }

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
     * @param $value
     * @return \Rx\Observable\AnonymousObservable
     */
    public static function just($value)
    {
        return new ReturnObservable($value);
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
            return $operator($this, $observer, $schedule);
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
        return $this->lift(new ReduceOperator($accumulator, $seed));
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
        return new NeverObservable();
    }

    /**
     * @param $error
     * @return \Rx\Observable\AnonymousObservable
     */
    public static function error($error)
    {
        return new ErrorObservable($error);
    }

    /**
     *  Invokes an action for each element in the observable sequence and invokes an action upon graceful or exceptional termination of the observable sequence.
     *  This method can be used for debugging, logging, etc. of query behavior by intercepting the message stream to run arbitrary actions for messages on the pipeline.
     *
     * @param ObserverInterface $observer
     *
     * @return \Rx\Observable\AnonymousObservable
     */
    public function doOnEach(ObserverInterface $observer)
    {
        return $this->lift(new DoOnEachOperator($observer));
    }

    public function doOnNext($onNext)
    {
        return $this->doOnEach(new CallbackObserver(
            $onNext
        ));
    }

    public function doOnError($onError)
    {
        return $this->doOnEach(new CallbackObserver(
            null,
            $onError
        ));
    }

    public function doOnCompleted($onCompleted)
    {
        return $this->doOnEach(new CallbackObserver(
            null,
            null,
            $onCompleted
        ));
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
     *  Creates an array from an observable sequence.
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

    /**
     * Returns the values from the source observable sequence only after the other observable sequence produces a value.
     *
     * @param mixed $other The observable sequence or Promise that triggers propagation of elements of the source sequence.
     * @return AnonymousObservable An observable sequence containing the elements of the source sequence starting from the point the other sequence triggered propagation.
     */
    public function skipUntil($other)
    {
        return $this->lift(new SkipUntilOperator($other));
    }

    /**
     *  Hides the identity of an observable sequence.
     * @return AnonymousObservable An observable sequence that hides the identity of the source sequence.
     */
    public function asObservable()
    {
        return $this->lift(new AsObservableOperator());
    }


    /**
     * Concatenates all the observable sequences.
     * @param ObservableInterface $observable
     * @return AnonymousObservable
     */
    public function concat(ObservableInterface $observable) {
        return $this->lift(new ConcatOperator($observable));
    }

    /**
     * Returns an observable sequence containing a value that represents how many elements in the specified observable sequence satisfy a condition if provided, else the count of items.
     *
     * @param callable $predicate
     * @return \Rx\Observable\AnonymousObservable
     */
    public function count($predicate = null) {
        return $this->lift(new CountOperator($predicate));
    }

    /**
     * Returns an observable sequence that invokes the specified factory function whenever a new observer subscribes.
     *
     * @param $factory
     * @return \Rx\Observable\AnonymousObservable
     */
    public static function defer($factory){
        return (new EmptyObservable())->lift(new DeferOperator($factory));
    }

    /**
     * Multicasts the source sequence notifications through an instantiated subject into all uses of the sequence within a selector function. Each
     * subscription to the resulting sequence causes a separate multicast invocation, exposing the sequence resulting from the selector function's
     * invocation. For specializations with fixed subject types, see Publish, PublishLast, and Replay.
     *
     * @param \Rx\Subject\Subject $subject
     * @param null $selector
     * @return \Rx\Observable\ConnectableObservable|\Rx\Observable\MulticastObservable
     */
    public function multicast(Subject $subject, $selector = null)
    {
        return $selector ?
          new MulticastObservable($this, function () use ($subject) {
              return $subject;
          }, $selector) :
          new ConnectableObservable($this, $subject);
    }

    /**
     * Multicasts the source sequence notifications through an instantiated subject from a subject selector factory, into all uses of the sequence within a selector function. Each
     * subscription to the resulting sequence causes a separate multicast invocation, exposing the sequence resulting from the selector function's
     * invocation. For specializations with fixed subject types, see Publish, PublishLast, and Replay.
     *
     * @param callable $subjectSelector
     * @param null $selector
     * @return \Rx\Observable\ConnectableObservable|\Rx\Observable\MulticastObservable
     */
    public function multicastWithSelector(callable $subjectSelector, $selector = null)
    {
        return new MulticastObservable($this, $subjectSelector, $selector);
    }

    /**
     * Returns an observable sequence that is the result of invoking the selector on a connectable observable sequence that shares a single subscription to the underlying sequence.
     * This operator is a specialization of Multicast using a regular Subject.
     *
     * @param callable|null $selector
     * @return \Rx\Observable\ConnectableObservable|\Rx\Observable\MulticastObservable
     */
    public function publish(callable $selector = null)
    {
        return $this->multicast(new Subject(), $selector);
    }

    /**
     * Returns an observable sequence that is the result of invoking the selector on a connectable observable sequence that shares a single subscription to the underlying sequence containing only the last notification.
     * This operator is a specialization of Multicast using a AsyncSubject.
     *
     * @param callable|null $selector
     * @return \Rx\Observable\ConnectableObservable|\Rx\Observable\MulticastObservable
     */
    public function publishLast(callable $selector = null)
    {
        return $this->multicast(new AsyncSubject(), $selector);
    }

    /**
     * Returns an observable sequence that is the result of invoking the selector on a connectable observable sequence that shares a single subscription to the underlying sequence and starts with initialValue.
     * This operator is a specialization of Multicast using a BehaviorSubject.
     *
     * @param null $initialValue
     * @param callable $selector
     * @return \Rx\Observable\ConnectableObservable|\Rx\Observable\MulticastObservable
     */
    public function publishValue($initialValue, callable $selector = null)
    {
        return $this->multicast(new BehaviorSubject($initialValue), $selector);
    }

    /**
     * Returns an observable sequence that shares a single subscription to the underlying sequence.
     * This operator is a specialization of publish which creates a subscription when the number of observers goes from zero to one, then shares that subscription with all subsequent observers until the number of observers returns to zero, at which point the subscription is disposed.
     *
     * @return \Rx\Observable\RefCountObservable An observable sequence that contains the elements of a sequence produced by multicasting the source sequence.,mk
     */
    public function share()
    {
        return $this->publish()->refCount();
    }

    /**
     * Returns an observable sequence that shares a single subscription to the underlying sequence and starts with an initialValue.
     * This operator is a specialization of publishValue which creates a subscription when the number of observers goes from zero to one, then shares that subscription with all subsequent observers until the number of observers returns to zero, at which point the subscription is disposed.
     *
     * @param $initialValue
     * @return \Rx\Observable\RefCountObservable
     */
    public function shareValue($initialValue)
    {
        return $this->publish($initialValue)->refCount();
    }

    /**
     * Returns an observable sequence that shares a single subscription to the underlying sequence replaying notifications subject to a maximum time length for the replay buffer.
     * This operator is a specialization of replay which creates a subscription when the number of observers goes from zero to one, then shares that subscription with all subsequent observers until the number of observers returns to zero, at which point the subscription is disposed.
     *
     * @param $bufferSize
     * @param $windowSize
     * @param $scheduler
     * @return \Rx\Observable\RefCountObservable
     */
    public function shareReplay($bufferSize, $windowSize, $scheduler)
    {
        return $this->replay(null, $bufferSize, $windowSize, $scheduler)->refCount();
    }

    /**
     * Returns an observable sequence that is the result of invoking the selector on a connectable observable sequence that shares a single subscription to the underlying sequence replaying notifications subject to a maximum time length for the replay buffer.
     * This operator is a specialization of Multicast using a ReplaySubject.
     *
     * @param callable|null $selector
     * @param null $bufferSize
     * @param null $windowSize
     * @param \Rx\SchedulerInterface|null $scheduler
     * @return \Rx\Observable\ConnectableObservable|\Rx\Observable\MulticastObservable
     */
    public function replay(callable $selector = null, $bufferSize = null, $windowSize = null, SchedulerInterface $scheduler = null)
    {
        return $this->multicast(new ReplaySubject($bufferSize, $windowSize, $scheduler), $selector);
    }

}
