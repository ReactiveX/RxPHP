<?php

namespace Rx\Observer;

use Exception;
use Rx\ObserverInterface;

abstract class AbstractObserver implements ObserverInterface
{
    /** @var bool */
    private $isStopped = false;

    /**
     * @return void
     */
    public function onCompleted()
    {
        if ($this->isStopped) {
            return;
        }

        $this->isStopped = true;
        $this->completed();
    }

    /**
     * @return void
     */
    public function onError(Exception $error)
    {
        if ($this->isStopped) {
            return;
        }

        $this->isStopped = true;
        $this->error($error);
    }

    /**
     * @return void
     */
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
     * @return void
     */
    abstract protected function next($value);

    /**
     * @return void
     */
    abstract protected function error(Exception $error);
}
