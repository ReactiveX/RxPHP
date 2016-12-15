<?php

namespace Rx\Testing;

use Rx\Disposable\CallbackDisposable;
use Rx\DisposableInterface;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

/**
 * Class TestSubject
 * @package Rx\Testing
 */
class TestSubject extends Subject
{
    /** @var int */
    private $subscribeCount;

    /** @var  ObserverInterface */
    private $observer;

    /* @var DisposableInterface[] */
    private $disposeOnMap;

    public function __construct()
    {
        $this->subscribeCount = 0;
    }

    public function subscribe(ObserverInterface $observer): DisposableInterface
    {

        $this->subscribeCount++;
        $this->observer = $observer;

        return new CallbackDisposable(function () {
            $this->dispose();
        });

    }

    /**
     * @param $value
     * @param $disposable
     */
    public function disposeOn($value, DisposableInterface $disposable): void
    {
        $this->disposeOnMap[$value] = $disposable;
    }

    /**
     * @param $value
     */
    public function onNext($value): void
    {
        $this->observer->onNext($value);
        if (isset($this->disposeOnMap[$value])) {
            $this->disposeOnMap[$value]->dispose();
        }
    }

    /**
     * @param \Exception $exception
     */
    public function onError(\Exception $exception): void
    {
        $this->observer->onError($exception);
    }

    public function onCompleted(): void
    {
        $this->observer->onCompleted();
    }

    /**
     * @return int
     */
    public function getSubscribeCount(): int
    {
        return $this->subscribeCount;
    }
}
