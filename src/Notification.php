<?php

namespace Rx;

/**
 * Represents a notification to an observer.
 */
abstract class Notification
{
    private $kind;
    private $hasValue;

    /**
     * @param mixed $kind Kind of notification
     * @param boolean $hasValue If the notification has a value
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
        return (string)$this === (string)$other;
    }

    abstract protected function doAcceptObservable(ObserverInterface $observer);

    abstract protected function doAccept($onNext, $onError, $onCompleted);
}
