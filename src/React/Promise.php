<?php

namespace Rx\React;

use React\Promise\Promise as ReactPromise;
use React\Promise\PromiseInterface;
use Rx\Disposable\CallbackDisposable;
use Rx\ObservableInterface;
use Rx\Observable;
use Rx\Observable\AnonymousObservable;
use Rx\Subject\AsyncSubject;
use React\Promise\Deferred;
use Throwable;

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
        $d->reject($exception instanceof Throwable ? $exception : new RejectedPromiseException($exception));
        return $d->promise();
    }

    /**
     * Converts an existing observable sequence to React Promise
     *
     * @throws \InvalidArgumentException
     */
    public static function fromObservable(ObservableInterface $observable, Deferred $deferred = null): ReactPromise
    {

        $d = $deferred ?: new Deferred(function () use (&$subscription): void {
            $subscription->dispose();
        });

        $value = null;

        $subscription = $observable->subscribe(
            function ($v) use (&$value): void {
                $value = $v;
            },
            function ($error) use ($d): void {
                $d->reject($error);
            },
            function () use ($d, &$value): void {
                $d->resolve($value);
            }
        );

        return $d->promise();
    }

    /**
     * Converts a Promise to an Observable sequence
     *
     * @throws \InvalidArgumentException
     */
    public static function toObservable(PromiseInterface $promise): Observable
    {
        $subject = new AsyncSubject();

        $p = $promise->then(
            function ($value) use ($subject): void {
                $subject->onNext($value);
                $subject->onCompleted();
            },
            function ($error) use ($subject): void {
                $error = $error instanceof \Throwable ? $error : new RejectedPromiseException($error);
                $subject->onError($error);
            }
        );

        return new AnonymousObservable(function ($observer) use ($subject, $p): \Rx\Disposable\CallbackDisposable {
            $disp = $subject->subscribe($observer);
            return new CallbackDisposable(function () use ($p, $disp): void {
                $disp->dispose();
                if (\method_exists($p, 'cancel')) {
                    $p->cancel();
                }
            });
        });
    }
}
