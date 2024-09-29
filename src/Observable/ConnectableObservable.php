<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\Disposable\BinaryDisposable;
use Rx\Disposable\CallbackDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

class ConnectableObservable extends Observable
{
    protected Subject $subject;

    protected BinaryDisposable $subscription;

    protected Observable $sourceObservable;

    protected bool $hasSubscription = false;

    public function __construct(
        Observable $source,
        Subject $subject = null
    ) {
        $this->sourceObservable = $source->asObservable();
        $this->subject          = $subject ?: new Subject();
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        return $this->subject->subscribe($observer);
    }

    public function connect(): DisposableInterface
    {
        if ($this->hasSubscription) {
            return $this->subscription;
        }

        $this->hasSubscription = true;

        $connectableDisposable = new CallbackDisposable(function (): void {
            $this->hasSubscription = false;
        });

        $this->subscription = new BinaryDisposable($this->sourceObservable->subscribe($this->subject), $connectableDisposable);

        return $this->subscription;
    }

    public function refCount(): RefCountObservable
    {
        return new RefCountObservable($this);
    }
}
