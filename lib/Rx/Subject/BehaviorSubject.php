<?php

namespace Rx\Subject;

use Rx\DisposableInterface;
use Rx\ObserverInterface;

class BehaviorSubject extends Subject
{
    private $value;

    public function __construct($initValue = null)
    {
        $this->value = $initValue;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function onNext($value)
    {
        $this->value = $value;

        return parent::onNext($value);
    }

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $disposable = parent::subscribe($observer, $scheduler);

        $observer->onNext($this->value);

        return $disposable;
    }

    public function dispose()
    {
        parent::dispose();

        unset($this->value);
    }
}
