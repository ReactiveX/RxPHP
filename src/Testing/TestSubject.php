<?php

declare(strict_types = 1);

namespace Rx\Testing;

use Rx\Disposable\CallbackDisposable;
use Rx\DisposableInterface;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

class TestSubject extends Subject
{
    private int $subscribeCount = 0;

    private ?\Rx\ObserverInterface $observer = null;

    /* @var DisposableInterface[] */
    private ?array $disposeOnMap = null;

    public function __construct()
    {
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {

        $this->subscribeCount++;
        $this->observer = $observer;

        return new CallbackDisposable(function (): void {
            $this->dispose();
        });

    }

    public function disposeOn($value, DisposableInterface $disposable): void
    {
        $this->disposeOnMap[$value] = $disposable;
    }

    public function onNext($value): void
    {
        $this->observer->onNext($value);
        if (isset($this->disposeOnMap[$value])) {
            $this->disposeOnMap[$value]->dispose();
        }
    }

    public function onError(\Throwable $exception): void
    {
        $this->observer->onError($exception);
    }

    public function onCompleted(): void
    {
        $this->observer->onCompleted();
    }

    public function getSubscribeCount(): int
    {
        return $this->subscribeCount;
    }
}
