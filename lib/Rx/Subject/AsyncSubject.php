<?php

namespace Rx\Subject;

use Rx\Disposable\EmptyDisposable;
use Rx\ObserverInterface;

/**
 * Class AsyncSubject
 * @package Rx\Subject
 */
class AsyncSubject extends Subject
{
    /**
     * @var
     */
    private $value;


    /**
     * @var bool
     */
    private $valueSet = false;

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     */
    public function onNext($value)
    {

        $this->assertNotDisposed();

        if ($this->isStopped) {
            return;
        }

        $this->value    = $value;
        $this->valueSet = true;
    }


    /**
     *
     */
    public function onCompleted()
    {
        if ($this->valueSet) {
            parent::onNext($this->value);
        }

        parent::onCompleted();
    }

    /**
     * @param \Rx\ObserverInterface $observer
     * @param null $scheduler
     * @return \Rx\Disposable\EmptyDisposable|\Rx\Subject\InnerSubscriptionDisposable
     */
    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $this->assertNotDisposed();

        if ($this->isStopped && $this->valueSet && !$this->exception) {
            $observer->onNext($this->value);
        }

        if (!$this->isStopped) {
            $this->observers[] = $observer;

            return new InnerSubscriptionDisposable($this, $observer);
        }

        if ($this->exception) {
            $observer->onError($this->exception);

            return new EmptyDisposable();
        }

        $observer->onCompleted();

        return new EmptyDisposable();

    }

    /**
     *
     */
    public function dispose()
    {
        parent::dispose();

        unset($this->value);
    }
}
