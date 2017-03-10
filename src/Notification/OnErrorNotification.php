<?php

declare(strict_types = 1);

namespace Rx\Notification;

use Rx\ObserverInterface;
use Rx\Notification;

class OnErrorNotification extends Notification
{
    private $exception;

    public function __construct(\Throwable $exception)
    {
        parent::__construct('E');

        $this->exception = $exception;
    }

    protected function doAcceptObservable(ObserverInterface $observer)
    {
        $observer->onError($this->exception);
    }

    protected function doAccept($onNext, $onError, $onCompleted)
    {
        $onError($this->exception);
    }

    public function __toString(): string
    {
        return 'OnError(' . get_class($this->exception) . ')';
    }
}
