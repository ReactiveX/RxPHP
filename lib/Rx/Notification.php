<?php

namespace Rx;

use Rx\ObserverInterface;

/**
 * Represents a notification to an observer.
 */
abstract class Notification
{
    private $kind;
    private $hasValue;

    /**
     * @param mixed   $kind     Kind of notification
     * @param boolean $hasValue If the notification has a value
     * @return void
     */
    public function __construct($kind, $hasValue = false)
    {
        $this->kind     = $kind;
        $this->hasValue = $hasValue;
    }

    public function accept($observerOrOnNext, $onError = null, $onCompleted = null)
    {
        if (null === $onError && null === $onCompleted && $observerOrOnNext instanceof ObserverInterface) {
            $this->doAcceptObservable($observerOrOnNext);

            return;
        }

        return $this->doAccept($observerOrOnNext, $onError, $onCompleted);
    }

    public function equals($other)
    {
        return (string) $this === (string) $other;
    }

    protected abstract function doAcceptObservable(ObserverInterface $observer);
    protected abstract function doAccept($onNext, $onError, $onCompleted);
}
