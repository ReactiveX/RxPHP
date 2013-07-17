<?php

namespace Rx\Notification;

use Exception;
use Rx\ObserverInterface;
use Rx\Notification;

class OnErrorNotification extends Notification
{
    private $exception;

    public function __construct(Exception $exception)
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

    public function __toString()
    {
        return 'OnError(' . $this->exception. ')';
    }
}
