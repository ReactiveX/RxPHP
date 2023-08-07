<?php

declare(strict_types = 1);

namespace Rx;

/**
 * Represents a notification to an observer.
 */
abstract class Notification
{
    /**
     * @template T
     * @param (callable(T): void)|ObserverInterface $observerOrOnNext
     * @param (callable(\Throwable): void) $onError
     * @param (callable(): void) $onCompleted
     * @return void
     */
    public function accept($observerOrOnNext, callable $onError = null, callable $onCompleted = null)
    {
        if (null === $onError && null === $onCompleted && $observerOrOnNext instanceof ObserverInterface) {
            $this->doAcceptObservable($observerOrOnNext);

            return;
        }

        assert(is_callable($observerOrOnNext));
        $this->doAccept($observerOrOnNext, $onError, $onCompleted);
    }

    /**
     * @param mixed $other
     */
    public function equals($other): bool
    {
        /** @phpstan-ignore-next-line */
        return (string)$this === (string)$other;
    }

    /**
     * @return void
     */
    abstract protected function doAcceptObservable(ObserverInterface $observer);

    /**
     * @template T
     * @param (callable(T): void) $onNext
     * @param (callable(\Throwable): void) $onError
     * @param (callable(): void) $onCompleted
     * @return void
     */
    abstract protected function doAccept(callable $onNext, callable $onError = null, callable $onCompleted = null);
}
