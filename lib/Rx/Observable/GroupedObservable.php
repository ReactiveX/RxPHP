<?php

namespace Rx\Observable;

use Rx\Observable;
use Rx\ObserverInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\Disposable\CompositeDisposable;
use Rx\DisposableInterface;

class GroupedObservable extends Observable
{
    /** @var string|int */
    private $key;

    /** @var ObservableInterface  */
    private $underlyingObservable;

    /**
     * @param string|int $key
     * @param ObservableInterface $underlyingObservable
     * @param DisposableInterface|null $mergedDisposable
     */
    public function __construct($key, ObservableInterface $underlyingObservable, DisposableInterface $mergedDisposable = null)
    {
        $this->key = $key;

        if (null === $mergedDisposable) {
            $this->underlyingObservable = $underlyingObservable;
        } else {
            $this->underlyingObservable = new AnonymousObservable(
                function ($observer, $scheduler) use ($mergedDisposable, $underlyingObservable) {
                    // todo, typehint $mergedDisposable?
                    return new CompositeDisposable([
                        $mergedDisposable->getDisposable(),
                        $underlyingObservable->subscribe($observer, $scheduler),
                    ]);
                }
            );
        }
    }

    /**
     * @return int|string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        return $this->underlyingObservable->subscribe($observer, $scheduler);
    }
}
