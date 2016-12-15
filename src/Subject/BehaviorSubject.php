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

    public function onNext($value): void
    {
        $this->value = $value;

        parent::onNext($value);
    }

    public function subscribe(ObserverInterface $observer): DisposableInterface
    {
        $disposable = parent::subscribe($observer);

        $observer->onNext($this->value);

        return $disposable;
    }

    public function dispose(): void
    {
        parent::dispose();

        unset($this->value);
    }
}
