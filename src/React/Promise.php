<?php

namespace Rx\React;

use React\Promise\CancellablePromiseInterface;
use React\Promise\Promise as ReactPromise;
use React\Promise\PromiseInterface;
use Rx\Disposable\CallbackDisposable;
use Rx\ObservableInterface;
use Rx\Observable;
use Rx\Observable\AnonymousObservable;
use Rx\Subject\AsyncSubject;
use React\Promise\Deferred;

final class Promise
{
    /**
     * @param mixed $value
     * @return ReactPromise A promise resolved to $value
     */
    public static function resolved($value): ReactPromise
    {
        $d = new Deferred();
        $d->resolve($value);
        return $d->promise();
    }

    /**
     * @param mixed $exception
     * @return ReactPromise A promise rejected with $exception
     */
    public static function rejected($exception): ReactPromise
    {
        $d = new Deferred();
        $d->reject($exception);
        return $d->promise();
    }

    /**
     * Converts an existing observable sequence to React Promise
     *
     * @param ObservableInterface $observable
     * @param Deferred $deferred
     * @return ReactPromise
     * @throws \InvalidArgumentException
     */
    public static function fromObservable(ObservableInterface $observable, Deferred $deferred = null): ReactPromise
    {

        $d = $deferred ?: new Deferred(function () use (&$subscription) {
            $subscription->dispose();
        });

        $value = null;

        $subscription = $observable->subscribe(
            function ($v) use (&$value) {
                $value = $v;
            },
            function ($error) use ($d) {
                $d->reject($error);
            },
            function () use ($d, &$value) {
                $d->resolve($value);
            }
        );

        return $d->promise();
    }

    /**
     * Converts a Promise to an Observable sequence
     *
     * @param PromiseInterface $promise
     * @return Observable
     * @throws \InvalidArgumentException
     */
    public static function toObservable(PromiseInterface $promise): Observable
    {
        $subject = new AsyncSubject();

        $p = $promise->then(
            function ($value) use ($subject) {
                $subject->onNext($value);
                $subject->onCompleted();
            },
            function ($error) use ($subject) {
                $error = $error instanceof \Exception ? $error : new RejectedPromiseException($error);
                $subject->onError($error);
            }
        );

        return new AnonymousObservable(function ($observer) use ($subject, $p) {
            $disp = $subject->subscribe($observer);
            return new CallbackDisposable(function () use ($p, $disp) {
                $disp->dispose();
                if ($p instanceof CancellablePromiseInterface) {
                    $p->cancel();
                }
            });
        });
    }
}
