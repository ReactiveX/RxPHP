<?php

declare(strict_types = 1);

namespace Rx\Notification;

use Rx\ObserverInterface;
use Rx\Notification;

class OnCompletedNotification extends Notification
{
    public function __construct()
    {
        parent::__construct('C');
    }

    protected function doAcceptObservable(ObserverInterface $observer)
    {
        $observer->onCompleted();
    }

    protected function doAccept($onNext, $onError, $onCompleted)
    {
        $onCompleted();
    }

    public function __toString(): string
    {
        return 'OnCompleted()';
    }
}
