<?php

namespace Rx\React;

use React\Promise\PromiseInterface;
use Rx\Disposable\CallbackDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observable;
use Rx\Observable\AnonymousObservable;
use Rx\Subject\AsyncSubject;
use React\Promise\Deferred;
use Throwable;
use function React\Promise\reject;
use function React\Promise\resolve;

/**
 * @template T
 */
final class Promise
{
    /**
     * @param T $value
     * @return PromiseInterface<T> A promise resolved to $value
     */
    public static function resolved($value): PromiseInterface
    {
        return resolve($value);
    }

    /**
     * @param mixed $exception
     * @return PromiseInterface<never> A promise rejected with $exception
     */
    public static function rejected($exception): PromiseInterface
    {
        return reject($exception instanceof Throwable ? $exception : new RejectedPromiseException($exception));
    }

    /**
     * Converts an existing observable sequence to React Promise
     *
     * @template X
     * @param ObservableInterface<X> $observable
     * @param ?Deferred<X> $deferred
     * @return PromiseInterface<X>
     * @throws \InvalidArgumentException
     */
    public static function fromObservable(ObservableInterface $observable, Deferred $deferred = null): PromiseInterface
    {
        /**
         * @var ?DisposableInterface $subscription
         */
        $subscription = null;
        $d = $deferred ?: new Deferred(function () use (&$subscription) {
            assert($subscription instanceof DisposableInterface);
            $subscription->dispose();
        });

        /**
         * @var X $value
         */
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
     * @template X
     * @param PromiseInterface<X> $promise
     * @return Observable<X>
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
            function (\Throwable $error) use ($subject) {
                $subject->onError($error);
            }
        );

        return new AnonymousObservable(function ($observer) use ($subject, $p) {
            $disp = $subject->subscribe($observer);
            return new CallbackDisposable(function () use ($p, $disp) {
                $disp->dispose();
                if (\method_exists($p, 'cancel')) {
                    $p->cancel();
                }
            });
        });
    }
}
