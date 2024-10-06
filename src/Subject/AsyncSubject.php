<?php

declare(strict_types = 1);

namespace Rx\Subject;

use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\ObserverInterface;

class AsyncSubject extends Subject
{
    private $value;

    private bool $valueSet = false;

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function onNext($value): void
    {
        $this->assertNotDisposed();

        if ($this->isStopped) {
            return;
        }

        $this->value    = $value;
        $this->valueSet = true;
    }

    public function onCompleted(): void
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

    public function dispose(): void
    {
        parent::dispose();

        unset($this->value);
    }
}
