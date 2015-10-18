<?php

namespace Rx\React;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use React\Promise\Deferred;

final class Promise
{
    /**
     * Converts an existing observable sequence to React Promise
     *
     * @param PromisorInterface|null $deferred
     * @return \React\Promise\Promise
     */
    public static function fromObservable(ObservableInterface $observable, Deferred $deferred = null)
    {
        $d     = $deferred ?: new Deferred();
        $value = null;

        $observable->subscribe(new CallbackObserver(
          function ($v) use (&$value) {
              $value = $v;
          },
          function ($error) use ($d) {
              $d->reject($error);
          },
          function () use ($d, &$value) {
              $d->resolve($value);
          }
        ));

        return $d->promise();
    }
}
