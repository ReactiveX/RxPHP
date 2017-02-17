<?php

namespace Rx;

use Rx\Observable\AnonymousObservable;
use Rx\Observable\ArrayObservable;
use Rx\Observable\ConnectableObservable;
use Rx\Observable\EmptyObservable;
use Rx\Observable\ErrorObservable;
use Rx\Observable\ForkJoinObservable;
use Rx\Observable\IntervalObservable;
use Rx\Observable\IteratorObservable;
use Rx\Observable\MulticastObservable;
use Rx\Observable\NeverObservable;
use Rx\Observable\RangeObservable;
use Rx\Observable\ReturnObservable;
use Rx\Observable\TimerObservable;
use Rx\Observer\CallbackObserver;
use Rx\Observer\DoObserver;
use Rx\Operator\AsObservableOperator;
use Rx\Operator\BufferWithCountOperator;
use Rx\Operator\CatchErrorOperator;
use Rx\Operator\CombineLatestOperator;
use Rx\Operator\ConcatAllOperator;
use Rx\Operator\ConcatMapOperator;
use Rx\Operator\ConcatOperator;
use Rx\Operator\CountOperator;
use Rx\Operator\DefaultIfEmptyOperator;
use Rx\Operator\DeferOperator;
use Rx\Operator\DelayOperator;
use Rx\Operator\DematerializeOperator;
use Rx\Operator\DistinctOperator;
use Rx\Operator\DistinctUntilChangedOperator;
use Rx\Operator\DoOnEachOperator;
use Rx\Operator\DoFinallyOperator;
use Rx\Operator\GroupByUntilOperator;
use Rx\Operator\IsEmptyOperator;
use Rx\Operator\MapOperator;
use Rx\Operator\FilterOperator;
use Rx\Operator\MinOperator;
use Rx\Operator\MaxOperator;
use Rx\Operator\MaterializeOperator;
use Rx\Operator\MergeAllOperator;
use Rx\Operator\RaceOperator;
use Rx\Operator\ReduceOperator;
use Rx\Operator\RepeatOperator;
use Rx\Operator\RepeatWhenOperator;
use Rx\Operator\RetryOperator;
use Rx\Operator\RetryWhenOperator;
use Rx\Operator\ScanOperator;
use Rx\Operator\SkipLastOperator;
use Rx\Operator\SkipOperator;
use Rx\Operator\SkipUntilOperator;
use Rx\Operator\StartWithArrayOperator;
use Rx\Operator\SkipWhileOperator;
use Rx\Operator\SubscribeOnOperator;
use Rx\Operator\SwitchFirstOperator;
use Rx\Operator\SwitchLatestOperator;
use Rx\Operator\TakeLastOperator;
use Rx\Operator\TakeOperator;
use Rx\Operator\TakeUntilOperator;
use Rx\Operator\TakeWhileOperator;
use Rx\Operator\ThrottleOperator;
use Rx\Operator\TimeoutOperator;
use Rx\Operator\TimestampOperator;
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
        $key = array_search($observer, $this->observers, true);

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
     * Creates an observable sequence from a specified subscribeAction callable implementation.
     *
     * @param callable $subscribeAction Implementation of the resulting observable sequence's subscribe method.
     * @return AnonymousObservable The observable sequence with the specified implementation for the subscribe method.
     *
     * @demo create/create.php
     * @operator
     * @reactivex create
     */
    public static function create(callable $subscribeAction)
    {
        return new AnonymousObservable($subscribeAction);
    }

    /**
     * Returns an Observable that emits an infinite sequence of ascending integers starting at 0, with a constant interval of time of your choosing between emissions.
     *
     * @param $interval int Period for producing the values in the resulting sequence (specified as an integer denoting milliseconds).
     * @param SchedulerInterface|null $scheduler
     * @return IntervalObservable An observable sequence that produces a value after each period.
     *
     * @demo interval/interval.php
     * @operator
     * @reactivex interval
     */
    public static function interval($interval, $scheduler = null)
    {
        return new IntervalObservable($interval, $scheduler);
    }

    /**
     * Returns an observable sequence that contains a single element.
     *
     * @param mixed $value Single element in the resulting observable sequence.
     * @return \Rx\Observable\AnonymousObservable An observable sequence with the single element.
     *
     * @demo just/just.php
     * @operator
     * @reactivex just
     */
    public static function just($value)
    {
        return new ReturnObservable($value);
    }

    /**
     * Returns an empty observable sequence.
     *
     * @return EmptyObservable An observable sequence with no elements.
     *
     * @demo empty-observable/empty-observable.php
     * @operator
     * @reactivex empty-never-throw
     */
    public static function emptyObservable()
    {
        return new EmptyObservable();
    }

    /**
     * Returns a non-terminating observable sequence, which can be used to denote an infinite duration.
     *
     * @return NeverObservable An observable sequence whose observers will never get called.
     *
     * @demo never/never.php
     * @operator
     * @reactivex empty-never-throw
     */
    public static function never()
    {
        return new NeverObservable();
    }

    /**
     * Returns an observable sequence that terminates with an exception.
     *
     * @param $error
     * @return ErrorObservable The observable sequence that terminates exceptionally with the specified exception object.
     *
     * @demo error-observable/error-observable.php
     * @operator
     * @reactivex empty-never-throw
     */
    public static function error(\Exception $error)
    {
        return new ErrorObservable($error);
    }

    /**
     * Combine an Observable together with another Observable by merging their emissions into a single Observable.
     *
     * @param ObservableInterface $otherObservable
     * @return AnonymousObservable
     *
     * @demo merge/merge.php
     * @operator
     * @reactivex merge
     */
    public function merge(ObservableInterface $otherObservable)
    {
        return (new AnonymousObservable(function (ObserverInterface $observer, SchedulerInterface $schedule) use ($otherObservable) {
            $observer->onNext($this);
            $observer->onNext($otherObservable);
            $observer->onCompleted();
        }))->mergeAll();
    }

    /**
     * Merges an observable sequence of observables into an observable sequence.
     *
     * @return AnonymousObservable
     *
     * @demo merge/merge-all.php
     * @operator
     * @reactivex merge
     */
    public function mergeAll()
    {
        /**
         * Calling 'Observable::mergeAll' statically has been deprecated and will be removed in the next release
         * This is only here for backwards compatibility.
         **/
        $static = !(isset($this) && get_class($this) == __CLASS__);
        $args   = func_get_args();
        if ($static && isset($args[0]) && $args[0] instanceof Observable) {
            return $args[0]->mergeAll();
        }

        return $this->lift(function () {
            return new MergeAllOperator($this);
        });
    }

    /**
     * Runs all observable sequences in parallel and collect their last elements.
     *
     * @param array $observables
     * @param callable|null $resultSelector
     * @param SchedulerInterface|null $scheduler
     * @return ForkJoinObservable
     *
     * @demo forkJoin-observable/forkJoin-observable.php
     * @operator
     * @reactivex forkJoin
     */
    public static function forkJoin(array $observables = [], callable $resultSelector = null, SchedulerInterface $scheduler = null)
    {
        return new ForkJoinObservable($observables, $resultSelector, $scheduler);
    }

    /**
     * Converts an array to an observable sequence
     *
     * @param array $array
     * @return ArrayObservable
     *
     * @demo fromArray/fromArray.php
     * @operator
     * @reactivex from
     */
    public static function fromArray(array $array)
    {
        return new ArrayObservable($array);
    }

    /**
     * Converts an Iterator into an observable sequence
     *
     * @param \Iterator $iterator
     * @return IteratorObservable
     *
     * @demo iterator/iterator.php
     * @operator
     * @reactivex from
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
     *
     * @demo defer/defer.php
     * @operator
     * @reactivex defer
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
     * @return RangeObservable
     *
     * @demo range/range.php
     * @operator
     * @reactivex range
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
     *
     * @demo start/start.php
     * @operator
     * @reactivex start
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
     * Takes a transforming function that operates on each element.
     *
     * @param callable $selector
     * @return AnonymousObservable
     *
     * @demo map/map.php
     * @operator
     * @reactivex map
     */
    public function map(callable $selector)
    {
        return $this->lift(function () use ($selector) {
            return new MapOperator($selector);
        });
    }

    /**
     * Maps operator variant that calls the map selector with the index and value
     *
     * @param callable $selector
     * @return AnonymousObservable
     *
     * @demo map/mapWithIndex.php
     * @operator
     * @reactivex map
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
     *
     * @demo map/mapTo.php
     * @operator
     * @reactivex map
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
     *
     * @operator
     * @reactivex map
     */
    public function select(callable $selector)
    {
        return $this->map($selector);
    }

    /**
     * Emit only those items from an Observable that pass a predicate test.
     *
     * @param callable $predicate
     * @return \Rx\Observable\AnonymousObservable
     *
     * @demo filter/filter.php
     * @operator
     * @reactivex filter
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
     *
     * @operator
     * @reactivex filter
     */
    public function where(callable $predicate)
    {
        return $this->filter($predicate);
    }

    /**
     * Projects each element of an observable sequence to an observable sequence and merges the resulting observable sequences into one observable sequence.
     *
     * @param callable $selector
     * @return AnonymousObservable
     *
     * @demo flatMap/flatMap.php
     * @operator
     * @reactivex flatMap
     */
    public function flatMap(callable $selector)
    {
        return $this->map($selector)->mergeAll();
    }

    /**
     * Projects each element of the source observable sequence to the other observable sequence and merges the
     * resulting observable sequences into one observable sequence.
     *
     * @param ObservableInterface $observable - An an observable sequence to project each element from the source
     * sequence onto.
     *
     * @return AnonymousObservable
     *
     * @demo concat/flatMapTo.php
     * @operator
     * @reactivex flatMap
     */
    public function flatMapTo(ObservableInterface $observable)
    {
        return $this->flatMap(function () use ($observable) {
            return $observable;
        });
    }

    /**
     * Alias for flatMap
     *
     * @param $selector
     * @return AnonymousObservable
     *
     * @operator
     * @reactivex flatMap
     */
    public function selectMany($selector)
    {
        return $this->flatMap($selector);
    }

    /**
     * Bypasses a specified number of elements in an observable sequence and then returns the remaining elements.
     *
     * Transform the items emitted by an Observable into Observables, and mirror those items emitted by the
     * most-recently transformed Observable.
     *
     * The flatMapLatest operator is similar to the flatMap and concatMap methods described above, however, rather than
     * emitting all of the items emitted by all of the Observables that the operator generates by transforming items
     * from the source Observable, flatMapLatest instead emits items from each such transformed Observable only until
     * the next such Observable is emitted, then it ignores the previous one and begins emitting items emitted by the
     * new one.
     *
     * @param callable $selector - A transform function to apply to each source element.
     * @return AnonymousObservable - An observable sequence which transforms the items emitted by an Observable into
     * Observables, and mirror those items emitted by the most-recently transformed Observable.
     *
     * @demo flatMap/flatMapLatest.php
     * @operator
     * @reactivex flatMap
     */
    public function flatMapLatest(callable $selector, SchedulerInterface $scheduler = null)
    {
        return $this->map($selector)->switchLatest($scheduler);
    }

    /**
     * @param integer $count
     * @return AnonymousObservable
     *
     * @demo skip/skip.php
     * @operator
     * @reactivex skip
     */
    public function skip($count)
    {
        return $this->lift(function () use ($count) {
            return new SkipOperator($count);
        });
    }

    /**
     * Bypasses elements in an observable sequence as long as a specified condition is true and then returns the
     * remaining elements.
     *
     * @param callable $predicate A function to test each element for a condition.
     *
     * @return AnonymousObservable An observable sequence that contains the elements from the input sequence starting
     * at the first element in the linear series that does not pass the test specified by predicate.
     *
     * @demo skip/skipWhile.php
     * @operator
     * @reactivex skipWhile
     */
    public function skipWhile(callable $predicate)
    {
        return $this->lift(function () use ($predicate) {
            return new SkipWhileOperator($predicate);
        });
    }

    /**
     * Bypasses elements in an observable sequence as long as a specified condition is true and then returns the
     * remaining elements. The element's index is used in the logic of the predicate function.
     *
     * @param callable $predicate A function to test each element for a condition; the first parameter of the
     * function represents the index of the source element, the second parameter is the value.
     *
     * @return AnonymousObservable An observable sequence that contains the elements from the input sequence starting
     * at the first element in the linear series that does not pass the test specified by predicate.
     *
     * @demo skip/skipWhileWithIndex.php
     * @operator
     * @reactivex skipWhile
     */
    public function skipWhileWithIndex(callable $predicate)
    {
        $index = 0;
        return $this->skipWhile(function ($value) use ($predicate, &$index) {
            return call_user_func_array($predicate, [$index++, $value]);
        });
    }

    /**
     * Returns a specified number of contiguous elements from the start of an observable sequence
     *
     * @param integer $count
     * @return AnonymousObservable|EmptyObservable
     *
     * @demo take/take.php
     * @operator
     * @reactivex take
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
     *
     * @demo take/take.php
     * @operator
     * @reactivex take
     */
    public function takeUntil(ObservableInterface $other)
    {
        return $this->lift(function () use ($other) {
            return new TakeUntilOperator($other);
        });
    }

    /**
     * Returns elements from an observable sequence as long as a specified condition is true.  It takes as a parameter a
     * a callback to test each source element for a condition.  The callback predicate is called with the value of the
     * element.
     *
     * @param callable $predicate
     * @return AnonymousObservable
     *
     * @demo take/takeWhile.php
     * @operator
     * @reactivex takeWhile
     */
    public function takeWhile(callable $predicate)
    {
        return $this->lift(function () use ($predicate) {
            return new TakeWhileOperator($predicate);
        });
    }

    /**
     * Returns elements from an observable sequence as long as a specified condition is true.  It takes as a parameter a
     * a callback to test each source element for a condition.  The callback predicate is called with the index and the
     * value of the element.
     *
     * @param callable $predicate
     * @return AnonymousObservable
     *
     * @demo take/takeWhileWithIndex.php
     * @operator
     * @reactivex takeWhile
     */
    public function takeWhileWithIndex(callable $predicate)
    {
        $index = 0;
        return $this->takeWhile(function ($value) use ($predicate, &$index) {
            return call_user_func_array($predicate, [$index++, $value]);
        });
    }

    /**
     * Returns a specified number of contiguous elements from the end of an observable sequence.
     *
     * @param $count
     * @return AnonymousObservable
     *
     * @demo take/takeLast.php
     * @operator
     * @reactivex takeLast
     */
    public function takeLast($count)
    {
        return $this->lift(function () use ($count) {
            return new TakeLastOperator($count);
        });
    }

    /**
     * Groups the elements of an observable sequence according to a specified key selector function and comparer and selects the resulting elements by using a specified function.
     *
     * @param callable $keySelector
     * @param callable|null $elementSelector
     * @param callable|null $keySerializer
     * @return AnonymousObservable
     *
     * @demo groupBy/groupBy.php
     * @operator
     * @reactivex groupBy
     */
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

    /**
     * Groups the elements of an observable sequence according to a specified key selector function and comparer and selects the resulting elements by using a specified function.
     *
     * @param callable $keySelector
     * @param callable|null $elementSelector
     * @param callable|null $durationSelector
     * @param callable|null $keySerializer
     * @return AnonymousObservable
     *
     * @demo groupBy/groupByUntil.php
     * @operator
     * @reactivex groupBy
     */
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
     * This method allows the use of extra operators with the namespace:
     * Rx\Operator
     * and also custom operators by adding an operator class with the
     * namespace format:
     * CustomNamespace\Rx\Operator\OperatorNameOperator
     *
     * @param $name
     * @param $arguments
     * @return Observable
     *
     * @demo custom-operator/rot13.php
     */
    public function __call($name, array $arguments)
    {
        $fullNamespace = 'Rx\\Operator\\';
        if ($name[0] === '_') {
            $parts = explode('_', $name);
            array_shift($parts);

            $methodName = array_pop($parts);
            $namespace = implode('\\', $parts);

            $name = $methodName;
            $fullNamespace = '\\' . $namespace . '\\' . $fullNamespace;
        }
        $className = $fullNamespace . ucfirst($name) . 'Operator';

        return $this->lift(function () use ($className, $arguments) {
            return (new \ReflectionClass($className))->newInstanceArgs($arguments);
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
     *
     * @demo reduce/reduce.php
     * @demo reduce/reduce-with-seed.php
     * @operator
     * @reactivex reduce
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
     *
     * @demo distinct/distinct.php
     * @operator
     * @reactivex distinct
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
     *
     * @demo distinct/distinctKey.php
     * @operator
     * @reactivex distinct
     */
    public function distinctKey(callable $keySelector, callable $comparer = null)
    {
        return $this->lift(function () use ($keySelector, $comparer) {
            return new DistinctOperator($keySelector, $comparer);
        });
    }

    /**
     * A variant of distinct that only compares emitted items from the source Observable against their immediate predecessors in order to determine whether or not they are distinct.
     *
     * @param callable $comparer
     * @return \Rx\Observable\AnonymousObservable
     *
     * @demo distinct/distinctUntilChanged.php
     * @operator
     * @reactivex distinct
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
     *
     * @demo distinct/distinctUntilKeyChanged.php
     * @operator
     * @reactivex distinct
     */
    public function distinctUntilKeyChanged(callable $keySelector = null, callable $comparer = null)
    {
        return $this->lift(function () use ($keySelector, $comparer) {
            return new DistinctUntilChangedOperator($keySelector, $comparer);
        });
    }

    /**
     * Invokes an action for each element in the observable sequence and invokes an action upon graceful
     * or exceptional termination of the observable sequence.
     * This method can be used for debugging, logging, etc. of query behavior by intercepting the message stream to
     * run arbitrary actions for messages on the pipeline.
     *
     * When using doOnEach, it is important to note that the Observer may receive additional
     * events after a stream has completed or errored (such as when useing a repeat or resubscribing).
     * If you are using an Observable that extends the AbstractObservable, you will not receive these
     * events. For this special case, use the DoObserver.
     *
     * doOnNext, doOnError, and doOnCompleted uses the DoObserver internally and will receive these
     * additional events.
     *
     * @param ObserverInterface $observer
     *
     * @return \Rx\Observable\AnonymousObservable
     *
     * @demo do/doOnEach.php
     * @operator
     * @reactivex do
     *
     */
    public function doOnEach(ObserverInterface $observer)
    {
        return $this->lift(function () use ($observer) {
            return new DoOnEachOperator($observer);
        });
    }

    /**
     * @param callable $onNext
     * @return AnonymousObservable
     *
     * @demo do/doOnNext.php
     * @operator
     * @reactivex do
     */
    public function doOnNext(callable $onNext)
    {
        return $this->doOnEach(new DoObserver(
            $onNext
        ));
    }

    /**
     * @param callable $onError
     * @return AnonymousObservable
     *
     * @demo do/doOnError.php
     * @operator
     * @reactivex do
     */
    public function doOnError(callable $onError)
    {
        return $this->doOnEach(new DoObserver(
            null,
            $onError
        ));
    }

    /**
     * @param callable $onCompleted
     * @return AnonymousObservable
     *
     * @demo do/doOnCompleted.php
     * @operator
     * @reactivex do
     */
    public function doOnCompleted(callable $onCompleted)
    {
        return $this->doOnEach(new DoObserver(
            null,
            null,
            $onCompleted
        ));
    }

    /**
     * Applies an accumulator function over an observable sequence and returns each intermediate result.
     * The optional seed value is used as the initial accumulator value.
     *
     * @param $accumulator
     * @param null $seed
     * @return AnonymousObservable
     *
     * @demo scan/scan.php
     * @demo scan/scan-with-seed.php
     * @operator
     * @reactivex scan
     */
    public function scan(callable $accumulator, $seed = null)
    {
        return $this->lift(function () use ($accumulator, $seed) {
            return new ScanOperator($accumulator, $seed);
        });
    }

    /**
     * Creates an observable sequence containing a single element which is an array containing all the elements of the source sequence.
     *
     * @return AnonymousObservable An observable sequence containing a single element with a list containing all the
     * elements of the source sequence.
     *
     * @demo toArray/toArray.php
     * @operator
     * @reactivex to
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
     *
     * @demo skip/skipLast.php
     * @operator
     * @reactivex skipLast
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
     *
     * @demo skip/skipUntil.php
     * @operator
     * @reactivex skipUntil
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
     *
     * @demo timer/timer.php
     * @operator
     * @reactivex timer
     */
    public static function timer($dueTime, SchedulerInterface $scheduler = null)
    {
        return new TimerObservable($dueTime, $scheduler);
    }

    /**
     * Hides the identity of an observable sequence.
     *
     * @return AnonymousObservable An observable sequence that hides the identity of the source sequence.
     *
     * @demo asObservable/asObservable.php
     * @operator
     * @reactivex from
     */
    public function asObservable()
    {
        return $this->lift(function () {
            return new AsObservableOperator();
        });
    }

    /**
     * Concatenate an observable sequence onto the end of the source observable.
     *
     * @param ObservableInterface $observable
     * @return AnonymousObservable
     *
     * @demo concat/concat.php
     * @operator
     * @reactivex concat
     */
    public function concat(ObservableInterface $observable)
    {
        return $this->lift(function () use ($observable) {
            return new ConcatOperator($observable);
        });
    }

    /**
     * Projects each element of an observable sequence to an observable sequence and concatenates the resulting
     * observable sequences into one observable sequence.
     *
     * @param callable $selector A transform function to apply to each element from the source sequence onto.
     * The selector is called with the following information:
     *   - the value of the element
     *   - the index of the element
     *   - the Observable object being subscribed
     *
     * @param callable $resultSelector A transform function to apply to each element of the intermediate sequence.
     * The resultSelector is called with the following information:
     *   - the value of the outer element
     *   - the value of the inner element
     *   - the index of the outer element
     *   - the index of the inner element
     *
     * @return AnonymousObservable - An observable sequence whose elements are the result of invoking the one-to-many
     * transform function collectionSelector on each element of the input sequence and then mapping each of those
     * sequence elements and their corresponding source element to a result element.
     *
     * @demo concat/concatMap.php
     * @operator
     * @reactivex flatMap
     */
    public function concatMap(callable $selector, callable $resultSelector = null)
    {
        return $this->lift(function () use ($selector, $resultSelector) {
            return new ConcatMapOperator($selector, $resultSelector);
        });
    }

    /**
     * Projects each element of the source observable sequence to the other observable sequence and merges the
     * resulting observable sequences into one observable sequence.
     *
     * @param ObservableInterface $observable - An an observable sequence to project each element from the source
     * sequence onto.
     *
     * @param callable $resultSelector A transform function to apply to each element of the intermediate sequence.
     * The resultSelector is called with the following information:
     *   - the value of the outer element
     *   - the value of the inner element
     *   - the index of the outer element
     *   - the index of the inner element
     *
     * @return AnonymousObservable An observable sequence whose elements are the result of invoking the one-to-many
     * transform function collectionSelector on each element of the input sequence and then mapping each of those
     * sequence elements and their corresponding source element to a result element.
     *
     * @demo concat/concatMapTo.php
     * @operator
     * @reactivex flatMap
     */
    public function concatMapTo(ObservableInterface $observable, callable $resultSelector = null)
    {
        return $this->concatMap(function () use ($observable) {
            return $observable;
        }, $resultSelector);
    }

    /**
     * Concatenates a sequence of observable sequences into a single observable sequence.
     *
     * @return AnonymousObservable The observable sequence that merges the elements of the inner sequences.
     *
     * @demo concat/concatAll.php
     * @operator
     * @reactivex concat
     */
    public function concatAll()
    {
        return $this->lift(function () {
            return new ConcatAllOperator();
        });
    }

    /**
     * Returns an observable sequence containing a value that represents how many elements in the specified observable
     * sequence satisfy a condition if provided, else the count of items.
     *
     * @param callable $predicate
     * @return \Rx\Observable\AnonymousObservable
     *
     * @demo count/count.php
     * @operator
     * @reactivex count
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
     *
     * @demo multicast/multicast.php
     * @operator
     * @reactivex publish
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
     *
     * @operator
     * @reactivex publish
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
     *
     * @demo publish/publish.php
     * @operator
     * @reactivex publish
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
     *
     * @demo publish/publishLast.php
     * @operator
     * @reactivex publish
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
     *
     * @demo publish/publishValue.php
     * @operator
     * @reactivex publish
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
     *
     * @demo share/share.php
     * @operator
     * @reactivex refcount
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
     *
     * @demo share/shareValue.php
     * @operator
     * @reactivex refcount
     */
    public function shareValue($initialValue)
    {
        return $this->publishValue($initialValue)->refCount();
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
     *
     * @demo replay/replay.php
     * @operator
     * @reactivex replay
     */
    public function replay(callable $selector = null, $bufferSize = null, $windowSize = null, SchedulerInterface $scheduler = null)
    {
        return $this->multicast(new ReplaySubject($bufferSize, $windowSize, $scheduler), $selector);
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
     *
     * @demo share/shareReplay.php
     * @operator
     * @reactivex replay
     */
    public function shareReplay($bufferSize, $windowSize = null, SchedulerInterface $scheduler = null)
    {
        return $this->replay(null, $bufferSize, $windowSize, $scheduler)->refCount();
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
     *
     * @demo zip/zip.php
     * @demo zip/zip-result-selector.php
     * @operator
     * @reactivex zip
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
     *
     * @demo retry/retry.php
     * @operator
     * @reactivex retry
     */
    public function retry($retryCount = -1)
    {
        return $this->lift(function () use ($retryCount) {
            return new RetryOperator($retryCount);
        });
    }

    /**
     * Repeats the source observable sequence on error when the notifier emits a next value. If the source observable
     * errors and the notifier completes, it will complete the source sequence.
     *
     * @param callable $notifier
     * @return AnonymousObservable
     *
     * @demo retry/retryWhen.php
     * @operator
     * @reactivex retry
     */
    public function retryWhen(callable $notifier)
    {
        return $this->lift(function () use ($notifier) {
            return new RetryWhenOperator($notifier);
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
     *
     * @demo combineLatest/combineLatest.php
     * @operator
     * @reactivex combinelatest
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
     *
     * @demo defaultIfEmpty/defaultIfEmpty.php
     * @operator
     * @reactivex defaultIfEmpty
     */
    public function defaultIfEmpty(ObservableInterface $observable)
    {
        return $this->lift(function () use ($observable) {
            return new DefaultIfEmptyOperator($observable);
        });
    }

    /**
     * Generates an observable sequence that repeats the given element the specified number of times.
     *
     * @param int $count
     * @return AnonymousObservable|EmptyObservable
     *
     * @demo repeat/repeat.php
     * @operator
     * @reactivex repeat
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
     * Returns an Observable that emits the same values as the source Observable with the exception of an onCompleted. 
     * An onCompleted notification from the source will result in the emission of a count item to the Observable provided 
     * as an argument to the notificationHandler function. If that Observable calls onComplete or onError then 
     * repeatWhen will call onCompleted or onError on the child subscription. Otherwise, this Observable will 
     * resubscribe to the source observable.
     *
     * @param callable $notifier
     * @return AnonymousObservable|EmptyObservable
     *
     * @demo repeat/repeatWhen.php
     * @operator
     * @reactivex repeat
     */
    public function repeatWhen(callable $notifier)
    {
        return $this->lift(function () use ($notifier) {
            return new RepeatWhenOperator($notifier);
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
     *
     * @demo delay/delay.php
     * @operator
     * @reactivex delay
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
     *
     * @demo timeout/timeout.php
     * @operator
     * @reactivex timeout
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
     *
     * @demo bufferWithCount/bufferWithCount.php
     * @demo bufferWithCount/bufferWithCountAndSkip.php
     * @operator
     * @reactivex buffer
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
     *
     * @demo catch/catchError.php
     * @operator
     * @reactivex catch
     */
    public function catchError(callable $selector)
    {
        return $this->lift(function () use ($selector) {
            return new CatchErrorOperator($selector);
        });
    }

    /**
     * Prepends a value to an observable sequence with an argument of a signal value to prepend.
     *
     * @param mixed $startValue
     * @return AnonymousObservable
     *
     * @demo startWith/startWith.php
     * @operator
     * @reactivex startwith
     */
    public function startWith($startValue)
    {
        return $this->startWithArray([$startValue]);
    }

    /**
     * Prepends a sequence of values to an observable sequence with an argument of an array of values to prepend.
     *
     * @param array $startArray
     * @return AnonymousObservable
     *
     * @demo startWith/startWithArray.php
     * @operator
     * @reactivex startwith
     */
    public function startWithArray(array $startArray)
    {
        return $this->lift(function () use ($startArray) {
            return new StartWithArrayOperator($startArray);
        });
    }
    
    /**
     * Returns the minimum value in an observable sequence according to the specified comparer.
     *
     * @param callable $comparer
     * @return AnonymousObservable
     *
     * @demo min/min.php
     * @demo min/min-with-comparer.php
     * @operator
     * @reactivex min
     */
    public function min(callable $comparer = null){
        return $this->lift(function () use ($comparer) {
            return new MinOperator($comparer);
        });
    }
    
    /**
     * Returns the maximum value in an observable sequence according to the specified comparer.
     *
     * @param callable $comparer
     * @return AnonymousObservable
     *
     * @demo max/max.php
     * @demo max/max-with-comparer.php
     * @operator
     * @reactivex max
     */
    public function max(callable $comparer = null)
    {
        return $this->lift(function () use ($comparer) {
            return new MaxOperator($comparer);
        });
    }

    /**
     * Materializes the implicit notifications of an observable sequence as explicit notifications.
     *
     * @return AnonymousObservable
     *
     * @operator
     * @reactivex materialize-dematerialize
     */
    public function materialize()
    {
        return $this->lift(function () {
            return new MaterializeOperator();
        });
    }

    /**
     * Dematerializes the explicit notification values of an observable sequence as implicit notifications.
     *
     * @return AnonymousObservable
     *
     * @operator
     * @reactivex materialize-dematerialize
     */
    public function dematerialize()
    {
        return $this->lift(function () {
            return new DematerializeOperator();
        });
    }

    /**
     * Records the timestamp for each value in an observable sequence.
     *
     * @param SchedulerInterface|null $scheduler
     * @return AnonymousObservable
     *
     * @demo timestamp/timestamp.php
     * @operator
     * @reactivex timestamp
     */
    public function timestamp(SchedulerInterface $scheduler = null)
    {
        return $this->lift(function () use ($scheduler) {
            return new TimestampOperator($scheduler);
        });
    }

    /**
     * Transforms an observable sequence of observable sequences into an observable sequence producing values only
     * from the most recent observable sequence.
     *
     * @return AnonymousObservable - The observable sequence that at any point in time produces the elements of the most
     * recent inner observable sequence that has been received.
     *
     * @demo switch/switchLatest.php
     * @operator
     * @reactivex switch
     */
    public function switchLatest(SchedulerInterface $scheduler = null)
    {
        return $this->lift(function () use ($scheduler) {
            return new SwitchLatestOperator($scheduler);
        });
    }


    /**
     * Receives an Observable of Observables and propagates the first Observable exclusively until it completes before
     * it begins subscribes to the next Observable. Observables that come before the current Observable completes will
     * be dropped and will not propagate.
     *
     * This operator is similar to concatAll() except that it will not hold onto Observables that come in before the
     * current one is finished completed.
     *
     * @return AnonymousObservable - An Observable sequence that is the result of concatenating non-overlapping items
     * emitted by an Observable of Observables.
     *
     * @demo switch/switchFirst.php
     * @operator
     * @reactivex switch
     */
    public function switchFirst()
    {
        return $this->lift(function () {
            return new SwitchFirstOperator();
        });
    }
    
    /**
     * Returns two observables which partition the observations of the source by the given function.
     * The first will trigger observations for those values for which the predicate returns true.
     * The second will trigger observations for those values where the predicate returns false.
     * The predicate is executed once for each subscribed observer.
     * Both also propagate all error observations arising from the source and each completes
     * when the source completes.
     *
     * @param callable $predicate
     * @return AnonymousObservable[]
     *
     * @demo partition/partition.php
     * @operator
     * @reactivex groupBy
     */
    public function partition(callable $predicate)
    {
        return [
            $this->filter($predicate),
            $this->filter(function () use ($predicate) {
                return !call_user_func_array($predicate, func_get_args());
            })
        ];
    }

    /**
     * Propagates the observable sequence that reacts first.  Also known as 'amb'.
     *
     * @param AnonymousObservable[] $observables
     * @return AnonymousObservable
     *
     * @demo race/race.php
     * @operator
     * @reactivex amb
     */
    public static function race(array $observables)
    {
        if (count($observables) === 1) {
            return $observables[0];
        }

        return (new ArrayObservable($observables))->lift(function () {
            return new RaceOperator();
        });
    }

    /**
     * Computes the sum of a sequence of values
     *
     * @return AnonymousObservable
     *
     * @demo sum/sum.php
     * @operator
     * @reactivex sum
     */
    public function sum()
    {
        return $this
            ->reduce(function ($a, $x) {
                return $a + $x;
            }, 0);
    }

    /**
     * Computes the average of an observable sequence of values.
     *
     * @return AnonymousObservable
     *
     * @demo average/average.php
     * @operator
     * @reactivex average
     */
    public function average()
    {
        return $this
            ->defaultIfEmpty(Observable::error(new \UnderflowException()))
            ->reduce(function ($a, $x) {
                static $count = 0;
                static $total = 0;

                $count++;
                $total += $x;

                return $total / $count;
            }, 0);
    }

    /**
     * Returns an Observable containing the value of a specified array index (if array) or property (if object) from
     * all elements in the Observable sequence. If a property can't be resolved the observable will error.
     *
     * @param mixed $property
     * @return Observable
     *
     * @demo pluck/pluck.php
     * @operator
     * @reactivex map
     */
    public function pluck($property)
    {
        $args = func_get_args();
        if (count($args) > 1) {
            return call_user_func_array([$this->pluck(array_shift($args)), 'pluck'], $args);
        }

        return $this->map(function ($x) use ($property) {
            if (is_array($x) && isset($x[$property])) {
                return $x[$property];
            }
            if (is_object($x) && isset($x->$property)) {
                return $x->$property;
            }

            throw new \Exception('Unable to pluck "' . $property . '"');
        });
    }

    /**
     * Returns an Observable that emits only the first item emitted by the source Observable during
     * sequential time windows of a specified duration.
     *
     * If items are emitted on the source observable prior to the expiration of the time period,
     * the last item emitted on the source observable will be emitted.
     *
     * @param $throttleDuration
     * @param null $scheduler
     * @return AnonymousObservable
     *
     * @demo throttle/throttle.php
     * @operator
     * @reactivex debounce
     */
    public function throttle($throttleDuration, $scheduler = null)
    {
        return $this->lift(function () use ($throttleDuration, $scheduler) {
            return new ThrottleOperator($throttleDuration, $scheduler);
        });
    }

    /**
     * If the source Observable is empty it returns an Observable that emits true, otherwise it emits false.
     *
     * @return AnonymousObservable
     *
     * @demo isEmpty/isEmpty.php
     * @demo isEmpty/isEmpty-false.php
     * @operator
     * @reactivex contains
     */
    public function isEmpty()
    {
        return $this->lift(function() {
            return new IsEmptyOperator();
        });
    }


    /**
     * Will call a specified function when the source terminates on complete or error.
     *
     * @param callable $callback
     * @return AnonymousObservable
     *
     * @demo do/doFinally.php
     * @demo do/doFinally-error.php
     * @operator
     * @reactivex do
     */
    public function doFinally(callable $callback)
    {
        return $this->lift(function() use ($callback) {
            return new DoFinallyOperator($callback);
        });
    }

    /**
     * Will apply given function to the source observable.
     *
     * @param callable $compose function that applies operators to source observable. Must return observable.
     * @return Observable
     *
     * @demo compose/compose.php
     */
    public function compose(callable $compose)
    {
        return $compose($this);
    }
}
