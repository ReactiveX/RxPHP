<?php

declare(strict_types = 1);

namespace Rx\Subject;

use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
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

    public function onCompleted()
    {
        if ($this->valueSet) {
            parent::onNext($this->value);
        }

        parent::onCompleted();
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
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

    public function dispose()
    {
        parent::dispose();

        unset($this->value);
    }
}
