<?php

declare(strict_types = 1);

namespace Rx\Subject;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

/**
 * @template T
 * @template-extends Subject<T>
 */
class BehaviorSubject extends Subject
{
    /**
     * @var T|null
     */
    private $value;

    /**
     * @param T $initValue
     */
    public function __construct($initValue = null)
    {
        $this->value = $initValue;
    }

    /**
     * @return ?T
     */
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
