<?php

declare(strict_types = 1);

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

        parent::onNext($value);
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $disposable = parent::_subscribe($observer);

        $observer->onNext($this->value);

        return $disposable;
    }

    public function dispose()
    {
        parent::dispose();

        unset($this->value);
    }
}
