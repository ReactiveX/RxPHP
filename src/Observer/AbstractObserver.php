<?php

declare(strict_types = 1);

namespace Rx\Observer;

use Rx\ObserverInterface;

abstract class AbstractObserver implements ObserverInterface
{
    /**
     * @var bool
     */
    private $isStopped = false;

    public function onCompleted()
    {
        if ($this->isStopped) {
            return;
        }

        $this->isStopped = true;
        $this->completed();
    }

    public function onError(\Throwable $error)
    {
        if ($this->isStopped) {
            return;
        }

        $this->isStopped = true;
        $this->error($error);
    }

    public function onNext($value)
    {
        if ($this->isStopped) {
            return;
        }

        $this->next($value);
    }

    /**
     * @return void
     */
    abstract protected function completed();

    /**
     * @template T
     * @param T $value
     * @return void
     */
    abstract protected function next($value);

    /**
     * @return void
     */
    abstract protected function error(\Throwable $error);
}
