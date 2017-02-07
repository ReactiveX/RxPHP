<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\Observable;
use Rx\ObserverInterface;
use Rx\ObservableInterface;
use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\RefCountDisposable;
use Rx\DisposableInterface;

class GroupedObservable extends Observable
{
    private $key;
    private $underlyingObservable;

    public function __construct($key, ObservableInterface $underlyingObservable, RefCountDisposable $mergedDisposable = null)
    {
        $this->key = $key;

        if (null === $mergedDisposable) {
            $this->underlyingObservable = $underlyingObservable;
        } else {
            $this->underlyingObservable = new AnonymousObservable(
                function ($observer) use ($mergedDisposable, $underlyingObservable) {
                    return new CompositeDisposable([
                        $mergedDisposable->getDisposable(),
                        $underlyingObservable->subscribe($observer),
                    ]);
                }
            );
        }
    }

    public function getKey()
    {
        return $this->key;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        return $this->underlyingObservable->subscribe($observer);
    }
}
