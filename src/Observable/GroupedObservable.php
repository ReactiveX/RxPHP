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
    private ObservableInterface $underlyingObservable;

    public function __construct(
        private $key,
        ObservableInterface $underlyingObservable,
        RefCountDisposable $mergedDisposable = null
    ) {
        $this->key = $key;

        $this->underlyingObservable = $mergedDisposable instanceof RefCountDisposable ?
            $this->newUnderlyingObservable($mergedDisposable, $underlyingObservable) :
            $underlyingObservable;
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
            function ($observer) use ($mergedDisposable, $underlyingObservable): \Rx\Disposable\BinaryDisposable {
                return new BinaryDisposable($mergedDisposable->getDisposable(), $underlyingObservable->subscribe($observer));
            }
        );
    }
}
