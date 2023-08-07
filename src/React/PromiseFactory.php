<?php

namespace Rx\React;

use React\Promise\PromiseInterface;
use Rx\Observable;
use Rx\ObservableInterface;

final class PromiseFactory
{
    /**
     * Returns an observable sequence that invokes the specified factory function whenever a new observer subscribes.
     *
     * @template T
     * @param (callable(): PromiseInterface<T>) $factory
     * @return Observable<T>
     * @throws \InvalidArgumentException
     */
    public static function toObservable(callable $factory): Observable
    {
        /** @phpstan-ignore-next-line */
        return Observable::defer(static function () use ($factory): ObservableInterface {
            return Promise::toObservable($factory());
        });
    }
}
