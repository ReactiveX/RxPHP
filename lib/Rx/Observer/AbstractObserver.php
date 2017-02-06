<?php

namespace Rx\Observer;

use Exception;
use Rx\ObserverInterface;

abstract class AbstractObserver implements ObserverInterface
{
    /** @var bool */
    private $isStopped = false;

    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @param mixed $value
     * @return void
     */
    abstract protected function next($value);

    /**
     * @param \Exception $error
     * @return void
     */
    abstract protected function error(Exception $error);
}
