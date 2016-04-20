<?php

namespace Rx\Notification;

use Rx\ObserverInterface;
use Rx\Notification;

class OnNextNotification extends Notification
{
    private $value;

    public function __construct($value)
    {
        parent::__construct('N', true);

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

    public function __toString()
    {
        return 'OnNext(' . json_encode($this->value) . ')';
    }
    
    public function equals($other)
    {
        if (($other instanceof $this) && is_object($this->value) && is_object($other->value)) {
            if ($this->value instanceof $other->value && method_exists($this->value, "equals")) {
                return $this->value->equals($other->value);
            }
        }
        
        return (string)$this === (string)$other;
    }
}
