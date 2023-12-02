<?php

declare(strict_types = 1);

namespace Rx\Notification;

use Rx\ObserverInterface;
use Rx\Notification;

class OnCompletedNotification extends Notification
{
    /**
     * @return void
     */
    protected function doAcceptObservable(ObserverInterface $observer)
    {
        $observer->onCompleted();
    }

    protected function doAccept(callable $onNext, callable $onError = null, callable $onCompleted = null)
    {
        assert(is_callable($onCompleted));
        $onCompleted();
    }

    public function __toString(): string
    {
        return 'OnCompleted()';
    }
}
