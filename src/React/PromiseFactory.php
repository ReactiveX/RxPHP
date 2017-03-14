<?php

namespace Rx\React;

use Rx\Observable;

final class PromiseFactory
{
    /**
     * Returns an observable sequence that invokes the specified factory function whenever a new observer subscribes.
     *
     * @param callable $factory
     * @return Observable
     * @throws \InvalidArgumentException
     */
    public static function toObservable(callable $factory): Observable
    {
        $observableFactory = function () use ($factory) {
            return Promise::toObservable($factory());
        };

        return Observable::defer($observableFactory);
    }
}
