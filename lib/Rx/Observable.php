<?php

namespace Rx;

use Rx\Observable\AnonymousObservable;
use Rx\Observable\ArrayObservable;
use Rx\Observable\ConnectableObservable;
use Rx\Observable\EmptyObservable;
use Rx\Observable\ErrorObservable;
use Rx\Observable\IntervalObservable;
use Rx\Observable\IteratorObservable;
use Rx\Observable\MulticastObservable;
use Rx\Observable\NeverObservable;
use Rx\Observable\RangeObservable;
use Rx\Observable\ReturnObservable;
use Rx\Observable\TimerObservable;
use Rx\Observer\CallbackObserver;
use Rx\Operator\AsObservableOperator;
use Rx\Operator\BufferWithCountOperator;
use Rx\Operator\CatchErrorOperator;
use Rx\Operator\CombineLatestOperator;
use Rx\Operator\ConcatOperator;
use Rx\Operator\CountOperator;
use Rx\Operator\DefaultIfEmptyOperator;
use Rx\Operator\DeferOperator;
use Rx\Operator\DelayOperator;
use Rx\Operator\DistinctOperator;
use Rx\Operator\DistinctUntilChangedOperator;
use Rx\Operator\DoOnEachOperator;
use Rx\Operator\GroupByUntilOperator;
use Rx\Operator\MapOperator;
use Rx\Operator\FilterOperator;
use Rx\Operator\MergeAllOperator;
use Rx\Operator\ReduceOperator;
use Rx\Operator\RepeatOperator;
use Rx\Operator\RetryOperator;
use Rx\Operator\ScanOperator;
use Rx\Operator\SkipLastOperator;
use Rx\Operator\SkipOperator;
use Rx\Operator\SkipUntilOperator;
use Rx\Operator\SubscribeOnOperator;
use Rx\Operator\TakeOperator;
use Rx\Operator\TakeUntilOperator;
use Rx\Operator\TimeoutOperator;
use Rx\Operator\ToArrayOperator;
use Rx\Operator\ZipOperator;
use Rx\Scheduler\ImmediateScheduler;
use Rx\Subject\AsyncSubject;
use Rx\Subject\BehaviorSubject;
use Rx\Subject\ReplaySubject;
use Rx\Subject\Subject;
use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\CallbackDisposable;

class Observable implements ObservableInterface
{
    protected $observers = [];
    protected $started = false;

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $this->observers[] = $observer;
        $this->started     = true;

        return new CallbackDisposable(function () use ($observer) {
            $this->removeObserver($observer);
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

    public function subscribeCallback(callable $onNext = null, callable  $onError = null, callable $onCompleted = null, SchedulerInterface $scheduler = null)
    {
        $observer = new CallbackObserver($onNext, $onError, $onCompleted);

        return $this->subscribe($observer, $scheduler);
    }

    /**
     * @param callable $subscribeAction
     * @return AnonymousObservable
     */
    public static function create(callable $subscribeAction)
    {
        return new AnonymousObservable($subscribeAction);
    }

    /**
     * Returns an observable sequence that produces a value after each period.
     *
     * @param $interval
     * @param SchedulerInterface|null $scheduler
     * @return IntervalObservable
     */
    public static function interval($interval, $scheduler = null)
    {
        return new IntervalObservable($interval, $scheduler);
    }

    /**
     * @param mixed $value
     * @return \Rx\Observable\AnonymousObservable
     */
    public static function just($value)
    {
        return new ReturnObservable($value);
    }

    /**
     * @return EmptyObservable
     */
    public static function emptyObservable()
    {
        return new EmptyObservable();
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
    public static function error(\Exception $error)
    {
        return new ErrorObservable($error);
    }

    /**
     * Merges an observable sequence of observables into an observable sequence.
     *
     * @param ObservableInterface $sources
     * @return AnonymousObservable
     */
    public static function mergeAll(ObservableInterface $sources)
    {
        return (new EmptyObservable())->lift(function () use ($sources) {
            return new MergeAllOperator($sources);
        });
    }

    /**
     * @param array $array
     * @return ArrayObservable
     */
    public static function fromArray(array $array)
    {
        return new ArrayObservable($array);
    }

    /**
     * @param \Iterator $iterator
     * @return IteratorObservable
     */
    public static function fromIterator(\Iterator $iterator)
    {
        return new IteratorObservable($iterator);
    }

    /**
     * Returns an observable sequence that invokes the specified factory function whenever a new observer subscribes.
     *
     * @param callable $factory
     * @return \Rx\Observable\AnonymousObservable
     */
    public static function defer(callable $factory)
    {
        return (new EmptyObservable())->lift(function () use ($factory) {
            return new DeferOperator($factory);
        });
    }

    /**
     * Generates an observable sequence of integral numbers within a specified range, using the specified scheduler to
     * send out observer messages.
     *
     * @param $start
     * @param $count
     * @return ArrayObservable
     */
    public static function range($start, $count, SchedulerInterface $scheduler = null)
    {
        return new RangeObservable($start, $count, $scheduler);
    }

    /**
     * Invokes the specified function asynchronously on the specified scheduler, surfacing the result through an
     * observable sequence.
     *
     * @param callable $action
     * @param SchedulerInterface $scheduler
     * @return AnonymousObservable
     */
    public static function start(callable $action, SchedulerInterface $scheduler = null)
    {
        $scheduler = $scheduler ?: new ImmediateScheduler();
        $subject   = new AsyncSubject();

        $scheduler->schedule(function () use ($subject, $action) {
            $result = null;
            try {
                $result = call_user_func($action);
            } catch (\Exception $e) {
                $subject->onError($e);
                return;
            }
            $subject->onNext($result);
            $subject->onCompleted();
        });

        return $subject->asObservable();
    }

    /**
     * @param callable $selector
     * @return AnonymousObservable
     */
    public function map(callable $selector)
    {
        return $this->lift(function () use ($selector) {
            return new MapOperator($selector);
        });
    }

    /**
     * Map operator variant that calls the map selector with the index and value
     *
     * @param callable $selector
     * @return AnonymousObservable
     */
    public function mapWithIndex(callable $selector)
    {
        $index = 0;
        return $this->map(function ($value) use ($selector, &$index) {
            return call_user_func_array($selector, [$index++, $value]);
        });
    }

    /**
     * Maps every value to the same value every time
     *
     * @param $value
     * @return AnonymousObservable
     */
    public function mapTo($value)
    {
        return $this->map(function () use ($value) {
            return $value;
        });
    }

    /**
     * Alias for Map
     *
     * @param callable $selector
     * @return \Rx\Observable\AnonymousObservable
     */
    public function select(callable $selector)
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
        return $this->lift(function () use ($predicate) {
            return new FilterOperator($predicate);
        });
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

    public function merge(ObservableInterface $otherObservable)
    {
        return self::mergeAll(
            self::fromArray([$this, $otherObservable])
        );
    }

    public function flatMap(callable $selector)
    {
        return self::mergeAll($this->select($selector));
    }

    /**
     * Alias for flatMap
     *
     * @param $selector
     * @return AnonymousObservable
     */
    public function selectMany($selector)
    {
        return $this->flatMap($selector);
    }

    /**
     * @param integer $count
     * @return AnonymousObservable
     */
    public function skip($count)
    {
        return $this->lift(function () use ($count) {
            return new SkipOperator($count);
        });
    }

    /**
     * @param integer $count
     * @return AnonymousObservable|EmptyObservable
     */
    public function take($count)
    {
        if ($count === 0) {
            return new EmptyObservable();
        }

        return $this->lift(function () use ($count) {
            return new TakeOperator($count);
        });
    }

    /**
     * Returns the values from the source observable sequence until the other observable sequence produces a value.
     *
     * @param ObservableInterface $other - other Observable sequence that terminates propagation of elements of
     * the source sequence.
     * @return AnonymousObservable - An observable sequence containing the elements of the source sequence up to the
     * point the other sequence interrupted further propagation.
     */
    public function takeUntil(ObservableInterface $other)
    {
        return $this->lift(function () use ($other) {
            return new TakeUntilOperator($other);
        });
    }

    public function groupBy(callable $keySelector, callable $elementSelector = null, callable $keySerializer = null)
    {
        return $this->groupByUntil($keySelector, $elementSelector, function () {

            // observable that never calls
            return new AnonymousObservable(function () {
                // todo?
                return new EmptyDisposable();
            });
        }, $keySerializer);
    }

    public function groupByUntil(callable $keySelector, callable $elementSelector = null, callable $durationSelector = null, callable $keySerializer = null)
    {
        return $this->lift(function () use ($keySelector, $elementSelector, $durationSelector, $keySerializer) {
            return new GroupByUntilOperator($keySelector, $elementSelector, $durationSelector, $keySerializer);
        });
    }

    /**
     * Lifts a function to the current Observable and returns a new Observable that when subscribed to will pass
     * the values of the current Observable through the Operator function.
     *
     * @param callable $operatorFactory
     * @return AnonymousObservable
     */
    public function lift(callable $operatorFactory)
    {
        return new AnonymousObservable(function (ObserverInterface $observer, SchedulerInterface $schedule) use ($operatorFactory) {
            $operator = $operatorFactory();
            return $operator($this, $observer, $schedule);
        });
    }

    /**
     * Applies an accumulator function over an observable sequence,
     * returning the result of the aggregation as a single element in the result sequence.
     * The specified seed value is used as the initial accumulator value.
     *
     * @param callable $accumulator - An accumulator function to be invoked on each element.
     * @param mixed $seed [optional] - The initial accumulator value.
     * @return \Rx\Observable\AnonymousObservable - An observable sequence containing a single element with the final
     * accumulator value.
     */
    public function reduce(callable $accumulator, $seed = null)
    {
        return $this->lift(function () use ($accumulator, $seed) {
            return new ReduceOperator($accumulator, $seed);
        });
    }

    /**
     * Returns an observable sequence that contains only distinct elements according to the keySelector and the
     * comparer. Usage of this operator should be considered carefully due to the maintenance of an internal lookup
     * structure which can grow large.
     *
     * @param callable|null $comparer
     * @return AnonymousObservable
     */
    public function distinct(callable $comparer = null)
    {
        return $this->lift(function () use ($comparer) {
            return new DistinctOperator(null, $comparer);
        });
    }

    /**
     *  Variant of distinct that takes a key selector
     *
     * @param callable|null $keySelector
     * @param callable|null $comparer
     * @return AnonymousObservable
     */
    public function distinctKey(callable $keySelector, callable $comparer = null)
    {
        return $this->lift(function () use ($keySelector, $comparer) {
            return new DistinctOperator($keySelector, $comparer);
        });
    }

    /**
     * Returns an observable sequence that contains only distinct contiguous elements according to the keySelector
     * and the comparer.
     *
     * @param callable $comparer
     * @return \Rx\Observable\AnonymousObservable
     */
    public function distinctUntilChanged(callable $comparer = null)
    {
        return $this->lift(function () use ($comparer) {
            return new DistinctUntilChangedOperator(null, $comparer);
        });
    }

    /**
     * Variant of distinctUntilChanged that takes a key selector
     * and the comparer.
     *
     * @param callable $keySelector
     * @param callable $comparer
     * @return \Rx\Observable\AnonymousObservable
     */
    public function distinctUntilKeyChanged(callable $keySelector = null, callable $comparer = null)
    {
        return $this->lift(function () use ($keySelector, $comparer) {
            return new DistinctUntilChangedOperator($keySelector, $comparer);
        });
    }

    /**
     *  Invokes an action for each element in the observable sequence and invokes an action upon graceful
     *  or exceptional termination of the observable sequence.
     *  This method can be used for debugging, logging, etc. of query behavior by intercepting the message stream to
     *  run arbitrary actions for messages on the pipeline.
     *
     * @param ObserverInterface $observer
     *
     * @return \Rx\Observable\AnonymousObservable
     */
    public function doOnEach(ObserverInterface $observer)
    {
        return $this->lift(function () use ($observer) {
            return new DoOnEachOperator($observer);
        });
    }

    public function doOnNext(callable $onNext)
    {
        return $this->doOnEach(new CallbackObserver(
            $onNext
        ));
    }

    public function doOnError(callable $onError)
    {
        return $this->doOnEach(new CallbackObserver(
            null,
            $onError
        ));
    }

    public function doOnCompleted(callable $onCompleted)
    {
        return $this->doOnEach(new CallbackObserver(
            null,
            null,
            $onCompleted
        ));
    }

    /**
     * Applies an accumulator function over an observable sequence and returns each intermediate result.
     * The optional seed value is used as the initial accumulator value.
     * For aggregation behavior with no intermediate results, see Observable.aggregate.
     *
     * @param $accumulator
     * @param null $seed
     * @return AnonymousObservable
     */
    public function scan(callable $accumulator, $seed = null)
    {
        return $this->lift(function () use ($accumulator, $seed) {
            return new ScanOperator($accumulator, $seed);
        });
    }

    /**
     * Creates an array from an observable sequence.
     * @return AnonymousObservable An observable sequence containing a single element with a list containing all the
     * elements of the source sequence.
     */
    public function toArray()
    {
        return $this->lift(function () {
            return new ToArrayOperator();
        });
    }

    /**
     * Bypasses a specified number of elements at the end of an observable sequence.
     *
     * This operator accumulates a queue with a length enough to store the first `count` elements. As more elements are
     * received, elements are taken from the front of the queue and produced on the result sequence. This causes
     * elements to be delayed.
     *
     * @param integer $count Number of elements to bypass at the end of the source sequence.
     * @return AnonymousObservable An observable sequence containing the source sequence elements except for the
     * bypassed ones at the end.
     */
    public function skipLast($count)
    {
        return $this->lift(function () use ($count) {
            return new SkipLastOperator($count);
        });
    }

    /**
     * Returns the values from the source observable sequence only after the other observable sequence produces a value.
     *
     * @param mixed $other The observable sequence that triggers propagation of elements of the source sequence.
     * @return AnonymousObservable An observable sequence containing the elements of the source sequence starting
     * from the point the other sequence triggered propagation.
     */
    public function skipUntil(ObservableInterface $other)
    {
        return $this->lift(function () use ($other) {
            return new SkipUntilOperator($other);
        });
    }

    /**
     * Returns an observable sequence that produces a value after dueTime has elapsed.
     *
     * @param integer $dueTime - milliseconds
     * @param SchedulerInterface $scheduler
     * @return TimerObservable
     */
    public static function timer($dueTime, SchedulerInterface $scheduler = null)
    {
        return new TimerObservable($dueTime, $scheduler);
    }

    /**
     * Hides the identity of an observable sequence.
     *
     * @return AnonymousObservable An observable sequence that hides the identity of the source sequence.
     */
    public function asObservable()
    {
        return $this->lift(function () {
            return new AsObservableOperator();
        });
    }

    /**
     * Concatenates all the observable sequences.
     *
     * @param ObservableInterface $observable
     * @return AnonymousObservable
     */
    public function concat(ObservableInterface $observable)
    {
        return $this->lift(function () use ($observable) {
            return new ConcatOperator($observable);
        });
    }

    /**
     * Returns an observable sequence containing a value that represents how many elements in the specified observable
     * sequence satisfy a condition if provided, else the count of items.
     *
     * @param callable $predicate
     * @return \Rx\Observable\AnonymousObservable
     */
    public function count(callable $predicate = null)
    {
        return $this->lift(function () use ($predicate) {
            return new CountOperator($predicate);
        });
    }

    /**
     * Multicasts the source sequence notifications through an instantiated subject into all uses of the sequence
     * within a selector function. Each subscription to the resulting sequence causes a separate multicast invocation,
     * exposing the sequence resulting from the selector function's invocation. For specializations with fixed subject
     * types, see Publish, PublishLast, and Replay.
     *
     * @param \Rx\Subject\Subject $subject
     * @param null $selector
     * @param SchedulerInterface $scheduler
     * @return ConnectableObservable|MulticastObservable
     */
    public function multicast(Subject $subject, $selector = null, SchedulerInterface $scheduler = null)
    {
        return $selector ?
            new MulticastObservable($this, function () use ($subject) {
                return $subject;
            }, $selector) :
            new ConnectableObservable($this, $subject, $scheduler);
    }

    /**
     * Multicasts the source sequence notifications through an instantiated subject from a subject selector factory,
     * into all uses of the sequence within a selector function. Each subscription to the resulting sequence causes a
     * separate multicast invocation, exposing the sequence resulting from the selector function's invocation.
     * For specializations with fixed subject types, see Publish, PublishLast, and Replay.
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
     * Returns an observable sequence that is the result of invoking the selector on a connectable observable sequence
     * that shares a single subscription to the underlying sequence.
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
     * Returns an observable sequence that is the result of invoking the selector on a connectable observable sequence
     * that shares a single subscription to the underlying sequence containing only the last notification.
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
     * Returns an observable sequence that is the result of invoking the selector on a connectable observable sequence
     * that shares a single subscription to the underlying sequence and starts with initialValue.
     * This operator is a specialization of Multicast using a BehaviorSubject.
     *
     * @param mixed $initialValue
     * @param callable $selector
     * @return \Rx\Observable\ConnectableObservable|\Rx\Observable\MulticastObservable
     */
    public function publishValue($initialValue, callable $selector = null)
    {
        return $this->multicast(new BehaviorSubject($initialValue), $selector);
    }

    /**
     * Returns an observable sequence that shares a single subscription to the underlying sequence.
     *
     * This operator is a specialization of publish which creates a subscription when the number of observers goes
     * from zero to one, then shares that subscription with all subsequent observers until the number of observers
     * returns to zero, at which point the subscription is disposed.
     *
     * @return \Rx\Observable\RefCountObservable An observable sequence that contains the elements of a sequence
     * produced by multicasting the source sequence.
     */
    public function share()
    {
        return $this->publish()->refCount();
    }

    /**
     * Returns an observable sequence that shares a single subscription to the underlying sequence and starts with an
     * initialValue.
     *
     * This operator is a specialization of publishValue which creates a subscription when the number of observers goes
     * from zero to one, then shares that subscription with all subsequent observers until the number of observers
     * returns to zero, at which point the subscription is disposed.
     *
     * @param $initialValue
     * @return \Rx\Observable\RefCountObservable
     */
    public function shareValue($initialValue)
    {
        return $this->publish($initialValue)->refCount();
    }

    /**
     * Returns an observable sequence that shares a single subscription to the underlying sequence replaying
     * notifications subject to a maximum time length for the replay buffer.
     *
     * This operator is a specialization of  replay which creates a subscription when the number of observers goes from
     * zero to one, then shares that  subscription with all subsequent observers until the number of observers returns
     * to zero, at which point the subscription is disposed.
     *
     * @param integer $bufferSize
     * @param integer $windowSize
     * @param $scheduler
     * @return \Rx\Observable\RefCountObservable
     */
    public function shareReplay($bufferSize, $windowSize, SchedulerInterface $scheduler)
    {
        return $this->replay(null, $bufferSize, $windowSize, $scheduler)->refCount();
    }

    /**
     * Returns an observable sequence that is the result of invoking the selector on a connectable observable sequence
     * that shares a single subscription to the underlying sequence replaying notifications subject to a maximum time
     * length for the replay buffer.
     *
     * This operator is a specialization of Multicast using a ReplaySubject.
     *
     * @param callable|null $selector
     * @param integer|null $bufferSize
     * @param integer|null $windowSize
     * @param \Rx\SchedulerInterface|null $scheduler
     * @return \Rx\Observable\ConnectableObservable|\Rx\Observable\MulticastObservable
     */
    public function replay(callable $selector = null, $bufferSize = null, $windowSize = null, SchedulerInterface $scheduler = null)
    {
        return $this->multicast(new ReplaySubject($bufferSize, $windowSize, $scheduler), $selector);
    }

    /**
     * Merges the specified observable sequences into one observable sequence by using the selector
     * function whenever all of the observable sequences have produced an element at a corresponding index. If the
     * result selector function is omitted, a list with the elements of the observable sequences at corresponding
     * indexes will be yielded.
     *
     * @param array $observables
     * @param callable $selector
     * @return \Rx\Observable\AnonymousObservable
     */
    public function zip(array $observables, callable $selector = null)
    {
        return $this->lift(function () use ($observables, $selector) {
            return new ZipOperator($observables, $selector);
        });
    }

    /**
     * Repeats the source observable sequence the specified number of times or until it successfully terminates.
     * If the retry count is not specified, it retries indefinitely. Note if you encounter an error and want it to
     * retry once, then you must use ->retry(2).
     *
     * @param int $retryCount
     * @return AnonymousObservable
     */
    public function retry($retryCount = -1)
    {
        return $this->lift(function () use ($retryCount) {
            return new RetryOperator($retryCount);
        });
    }

    /**
     * Merges the specified observable sequences into one observable sequence by using the selector function whenever
     * any of the observable sequences produces an element. Observables need to be an array.
     * If the result selector is omitted, a list with the elements will be yielded.
     *
     * @param array $observables
     * @param callable|null $selector
     * @return AnonymousObservable
     */
    public function combineLatest(array $observables, callable $selector = null)
    {
        return $this->lift(function () use ($observables, $selector) {
            return new CombineLatestOperator($observables, $selector);
        });
    }

    /**
     * Returns the specified value of an observable if the sequence is empty.
     *
     * @param ObservableInterface $observable
     * @return AnonymousObservable
     */
    public function defaultIfEmpty(ObservableInterface $observable)
    {
        return $this->lift(function () use ($observable) {
            return new DefaultIfEmptyOperator($observable);
        });
    }

    /**
     * @param int $count
     * @return AnonymousObservable|EmptyObservable
     */
    public function repeat($count = -1)
    {
        if ($count == 0) {
            return new EmptyObservable();
        }

        return $this->lift(function () use ($count) {
            return new RepeatOperator($count);
        });
    }

    /**
     * Wraps the source sequence in order to run its subscription and unsubscription logic on the specified scheduler.
     *
     * @param SchedulerInterface $scheduler
     * @return AnonymousObservable
     */
    public function subscribeOn(SchedulerInterface $scheduler)
    {
        return $this->lift(function () use ($scheduler) {
            return new SubscribeOnOperator($scheduler);
        });
    }

    /**
     * Time shifts the observable sequence by dueTime. The relative time intervals between the values are preserved.
     *
     * @param $delay
     * @param SchedulerInterface|null $scheduler
     * @return AnonymousObservable
     */
    public function delay($delay, $scheduler = null)
    {
        return $this->lift(function () use ($delay, $scheduler) {
            return new DelayOperator($delay, $scheduler);
        });
    }

    /**
     * @param $timeout
     * @param ObservableInterface $timeoutObservable
     * @param SchedulerInterface $scheduler
     * @return AnonymousObservable
     */
    public function timeout($timeout, ObservableInterface $timeoutObservable = null, SchedulerInterface $scheduler = null)
    {
        return $this->lift(function () use ($timeout, $timeoutObservable, $scheduler) {
            return new TimeoutOperator($timeout, $timeoutObservable, $scheduler);
        });
    }

    /**
     * Projects each element of an observable sequence into zero or more buffers which are produced based on
     * element count information.
     *
     * @param $count
     * @param int $skip
     * @return AnonymousObservable
     */
    public function bufferWithCount($count, $skip = null)
    {
        return $this->lift(function () use ($count, $skip) {
            return new BufferWithCountOperator($count, $skip);
        });
    }

    /**
     * Continues an observable sequence that is terminated by an exception with the next observable sequence.
     *
     * @param callable $selector
     * @return AnonymousObservable
     */
    public function catchError(callable $selector)
    {
        return $this->lift(function () use ($selector) {
            return new CatchErrorOperator($selector);
        });
    }
}
