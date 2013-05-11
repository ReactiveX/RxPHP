<?php

namespace Rx\Observable;

use Exception;
use InvalidArgumentException;
use Rx\ObserverInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\Scheduler\ImmediateScheduler;

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
        return new AnonymousObservable(function($observer) use ($currentObservable, $selector) {
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

            $currentObservable->subscribe($selectObserver);
        });
    }

    public function where($predicate)
    {
        if ( ! is_callable($predicate)) {
            throw new InvalidArgumentException('Predicate should be a callable.');
        }

        $currentObservable = $this;

        // todo: add scheduler
        return new AnonymousObservable(function($observer) use ($currentObservable, $predicate) {
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

            $currentObservable->subscribe($selectObserver);
        });
    }
}
