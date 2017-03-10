<?php

declare(strict_types = 1);

namespace Rx\Notification;

use Rx\ObserverInterface;
use Rx\Notification;

class OnNextNotification extends Notification
{
    private $value;

    public function __construct($value)
    {
        parent::__construct('N');

        $this->value = $value;
    }

    protected function doAcceptObservable(ObserverInterface $observer)
    {
        $observer->onNext($this->value);
    }

    protected function doAccept($onNext, $onError, $onCompleted)
    {
        $onNext($this->value);
    }

    public function __toString(): string
    {
        return 'OnNext(' . json_encode($this->value) . ')';
    }

    public function equals($other): bool
    {
        if (($other instanceof $this) && is_object($this->value) && is_object($other->value)) {
            if ($this->value instanceof $other->value && method_exists($this->value, "equals")) {
                return $this->value->equals($other->value);
            }
        }

        return (string)$this === (string)$other;
    }
}
