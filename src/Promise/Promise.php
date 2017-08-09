<?php

namespace Rx\Promise;

use Amp\Deferred as AmpDeferred;
use Amp\Promise as AmpPromise;
use React\Promise\CancellablePromiseInterface as CancellableReactPromise;
use React\Promise\Deferred as ReactDeferred;
use React\Promise\ExtendedPromiseInterface as ExtendedReactPromise;
use React\Promise\PromiseInterface as ReactPromise;
use Rx\Disposable\CallbackDisposable;
use Rx\Observable;
use Rx\Observable\AnonymousObservable;
use Rx\Subject\AsyncSubject;

final class Promise implements PromiseInterface
{
    /** @var \Rx\Observable */
    private $observable;

    /**
     * @param ReactPromise|AmpPromise $promise
     *
     * @return \Rx\Observable
     * @throws \TypeError If $promise is not a React promise or Amp promise.
     */
    public static function toObservable($promise): Observable
    {
        $subject = new AsyncSubject();

        if ($promise instanceof ReactPromise) {
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
                    if ($p instanceof CancellableReactPromise) {
                        $p->cancel();
                    }
                });
            });
        }

        if ($promise instanceof AmpPromise) {
            $promise->onResolve(function ($error, $value) use ($subject) {
                if ($error) {
                    $error = $error instanceof \Exception ? $error : new RejectedPromiseException($error);
                    $subject->onError($error);
                    return;
                }

                $subject->onNext($value);
                $subject->onCompleted();
            });

            return new AnonymousObservable(function ($observer) use ($subject) {
                $disp = $subject->subscribe($observer);
                return new CallbackDisposable([$disp, "dispose"]);
            });
        }

        throw new \TypeError(
            \sprintf("Promise must be an instance of %s or %s", ReactPromise::class, AmpPromise::class)
        );
    }

    public static function fromObservable(Observable $observable): self
    {
        return new self($observable);
    }

    protected function __construct(Observable $observable)
    {
        $this->observable = $observable;
    }

    private function toReactPromise(): ExtendedReactPromise
    {
        static $enabled = false;

        // @codeCoverageIgnoreStart
        if (!$enabled && !($enabled = \interface_exists(ReactPromise::class))) {
            throw new \Error("Cannot use an observable as a React promise without installing react/promise");
        } // @codeCoverageIgnoreEnd

        $deferred = new ReactDeferred();

        $this->observable
            ->flatMap(function ($v) {
                if ($v instanceof ReactPromise || $v instanceof AmpPromise) {
                    return self::toObservable($v);
                }

                return Observable::of($v);
            })
            ->subscribe(
                function ($v) use (&$value) {
                    $value = $v;
                },
                function ($error) use ($deferred) {
                    $deferred->reject($error);
                },
                function () use (&$value, $deferred) {
                    $deferred->resolve($value);
                }
            );

        return $deferred->promise();
    }

    private function toAmpPromise(): AmpPromise
    {
        static $enabled = false;

        // @codeCoverageIgnoreStart
        if (!$enabled && !($enabled = \interface_exists(AmpDeferred::class))) {
            throw new \Error("Cannot use an observable as an Amp promise without installing amphp/amp");
        } // @codeCoverageIgnoreEnd

        $deferred = new AmpDeferred();

        $this->observable
            ->flatMap(function ($v) {
                if ($v instanceof ReactPromise || $v instanceof AmpPromise) {
                    return self::toObservable($v);
                }

                return Observable::of($v);
            })
            ->subscribe(
                function ($v) use (&$value) {
                    $value = $v;
                },
                function ($error) use ($deferred) {
                    $deferred->fail($error);
                },
                function () use (&$value, $deferred) {
                    $deferred->resolve($value);
                }
            );

        return $deferred->promise();
    }

    /**
     * @param callable $onResolve
     */
    public function onResolve(callable $onResolve)
    {
        $this->toAmpPromise()->onResolve($onResolve);
    }

    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onProgress
     *
     * @return ReactPromise
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null): ReactPromise
    {
        return $this->toReactPromise()->then($onFulfilled, $onRejected, $onProgress);
    }

    /**
     * @return void
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        $this->toReactPromise()->done($onFulfilled, $onRejected, $onProgress);
    }

    /**
     * @param callable $onRejected
     *
     * @return ExtendedReactPromise
     */
    public function otherwise(callable $onRejected): ExtendedReactPromise
    {
        return $this->toReactPromise()->otherwise($onRejected);
    }

    /**
     * @return ExtendedReactPromise
     */
    public function always(callable $onFulfilledOrRejected): ExtendedReactPromise
    {
        return $this->toReactPromise()->always($onFulfilledOrRejected);
    }

    /**
     * @return ExtendedReactPromise
     */
    public function progress(callable $onProgress): ExtendedReactPromise
    {
        return $this->toReactPromise()->progress($onProgress);
    }
}
