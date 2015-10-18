<?php

namespace Rx\React;

use Rx\ObservableInterface;
use Rx\Observable\BaseObservable;
use Rx\Observer\CallbackObserver;
use Rx\Subject\AsyncSubject;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

final class PromiseFactory
{
    /**
     * Returns an observable sequence that invokes the specified factory function whenever a new observer subscribes.
     *
     * @param callable $factory
     * @return \Rx\Observable\AnonymousObservable
     */
    public static function toObservable($factory)
    {
        if ( ! is_callable($factory)) {
            throw new InvalidArgumentException('Factory should be a callable.');
        }

        $observableFactory = function() use ($factory) {
            return Promise::toObservable($factory());
        };

        return BaseObservable::defer($observableFactory);
    }
}
