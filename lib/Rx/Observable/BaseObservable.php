<?php

namespace Rx\Observable;

use Exception;
use InvalidArgumentException;
use Rx\ObserverInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\Scheduler\ImmediateScheduler;
use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\SingleAssignmentDisposable;
use Rx\Subject\Subject;
use Rx\Disposable\RefCountDisposable;
use Rx\Disposable\EmptyDisposable;

abstract class BaseObservable implements ObservableInterface
{
    protected $observers = array();
    protected $started = false;
    private $disposable = null;

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $this->observers[] = $observer;

        if ( ! $this->started) {
            $this->disposable = $this->start($scheduler);
        }

        return $this->disposable;
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

        return $this->doStart($scheduler);
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

    public static function fromArray(array $array, $scheduler = null)
    {
        if (null === $scheduler) {
            $scheduler = new ImmediateScheduler();
        }

        return new AnonymousObservable(function ($observer) use ($array, &$scheduler) {
            $count = 0;

            return $scheduler->scheduleRecursive(function($reschedule) use (&$count, $array, $observer) {
                if ($count < count($array)) {
                    $observer->onNext($array[$count]);
                    $count++;
                    $reschedule();
                } else {
                    $observer->onCompleted();
                }
            });
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
                array($observer, 'onCompleted')
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

        if (null === $keySerializer) {
            $keySerializer = function($elem) { return $elem; };
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
                        $expire = function() use (&$map, &$md, $serializedKey, &$writer) {
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
                        $writer->onError($exception);
                    }

                    $observer->onError($exception);
                },
                function() use (&$map, $observer) {
                    foreach ($map as $writer) {
                        $writer->onCompleted();
                    }

                    $observer->onCompleted();
                }
            ));

            return $refCountDisposable;
        });
    }
}
