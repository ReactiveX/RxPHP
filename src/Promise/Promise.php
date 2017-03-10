<?php

declare(strict_types = 1);

namespace Rx\Promise;

use Interop\Async\Promise as InteropPromise;
use Rx\Observable;

final class Promise implements InteropPromise
{
    private $observable;

    public function __construct(Observable $observable)
    {
        $this->observable = $observable;
    }

    /**
     * Registers a callback to be invoked when the promise is resolved.
     *
     * @param callable (\Throwable|\Exception|null $exception, mixed $result) $onResolved
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function when(callable $onResolved)
    {
        $value = null;

        $this->observable
            ->flatMap(function ($v) {
                return $v instanceof InteropPromise ? Observable::fromPromise($v) : Observable::of($v);
            })
            ->subscribe(
                function ($v) use (&$value) {
                    $value = $v;
                },
                function (\Throwable $e) use (&$value, $onResolved) {
                    try {
                        $onResolved($e, $value);
                    } catch (\Throwable $ex) {
                        InteropPromise\ErrorHandler::notify($ex);
                    }
                },
                function () use (&$value, $onResolved) {
                    try {
                        $onResolved(null, $value);
                    } catch (\Throwable $ex) {
                        InteropPromise\ErrorHandler::notify($ex);
                    }
                });
    }
}
