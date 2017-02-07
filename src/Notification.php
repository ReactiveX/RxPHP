<?php

declare(strict_types = 1);

namespace Rx;

/**
 * Represents a notification to an observer.
 */
abstract class Notification
{
    private $kind;

    /**
     * @param mixed $kind Kind of notification
     */
    public function __construct($kind)
    {
        $this->kind = $kind;
    }

    public function accept($observerOrOnNext, $onError = null, $onCompleted = null)
    {
        if (null === $onError && null === $onCompleted && $observerOrOnNext instanceof ObserverInterface) {
            $this->doAcceptObservable($observerOrOnNext);

            return;
        }

        return $this->doAccept($observerOrOnNext, $onError, $onCompleted);
    }

    public function equals($other): bool
    {
        return (string)$this === (string)$other;
    }

    abstract protected function doAcceptObservable(ObserverInterface $observer);

    abstract protected function doAccept($onNext, $onError, $onCompleted);
}
