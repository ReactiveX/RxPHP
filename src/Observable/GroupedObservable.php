<?php

declare(strict_types=1);

namespace Rx\Observable;

use Rx\Disposable\BinaryDisposable;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\ObservableInterface;
use Rx\Disposable\RefCountDisposable;
use Rx\DisposableInterface;

class GroupedObservable extends Observable
{
    private $key;
    private $underlyingObservable;

    public function __construct($key, ObservableInterface $underlyingObservable, RefCountDisposable $mergedDisposable = null)
    {
        $this->key = $key;

        $this->underlyingObservable = !$mergedDisposable ?
            $underlyingObservable :
            $this->newUnderlyingObservable($mergedDisposable, $underlyingObservable);
    }

    public function getKey()
    {
        return $this->key;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        return $this->underlyingObservable->subscribe($observer);
    }

    private function newUnderlyingObservable(RefCountDisposable $mergedDisposable, ObservableInterface $underlyingObservable): Observable
    {
        return new AnonymousObservable(
            function ($observer) use ($mergedDisposable, $underlyingObservable) {
                return new BinaryDisposable($mergedDisposable->getDisposable(), $underlyingObservable->subscribe($observer));
            }
        );
    }
}
