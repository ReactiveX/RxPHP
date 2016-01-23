<?php

namespace Rx\Testing;

use Rx\Disposable\CallbackDisposable;
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

    /**
     * TestSubject constructor.
     */
    public function __construct()
    {
        $this->subscribeCount = 0;
    }

    /**
     * @param \Rx\ObserverInterface $observer
     * @param null $scheduler
     * @return \Rx\Disposable\CallbackDisposable
     */
    public function subscribe(ObserverInterface $observer, $scheduler = null)
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
    public function disposeOn($value, $disposable)
    {
        $this->disposeOnMap[$value] = $disposable;
    }

    /**
     * @param $value
     */
    public function onNext($value)
    {
        $this->observer->onNext($value);
        if (isset($this->disposeOnMap[$value])) {
            $this->disposeOnMap[$value]->dispose();
        }
    }

    /**
     * @param \Exception $exception
     */
    public function onError(\Exception $exception)
    {
        $this->observer->onError($exception);
    }

    /**
     *
     */
    public function onCompleted()
    {
        $this->observer->onCompleted();
    }

    /**
     * @return int
     */
    public function getSubscribeCount()
    {
        return $this->subscribeCount;
    }
}
