<?php

declare(strict_types = 1);

namespace Rx\Notification;

use Rx\ObserverInterface;
use Rx\Notification;

class OnErrorNotification extends Notification
{
    /**
     * @var \Throwable
     */
    private $exception;

    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return void
     */
    protected function doAcceptObservable(ObserverInterface $observer)
    {
        $observer->onError($this->exception);
    }

    protected function doAccept(callable $onNext, callable $onError = null, callable $onCompleted = null)
    {
        assert(is_callable($onError));
        $onError($this->exception);
    }

    public function __toString(): string
    {
        return 'OnError(' . get_class($this->exception) . ')';
    }
}
